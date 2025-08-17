<?php

namespace App\Http\Controllers\Api;

use App\Events\ConversationUpdated;
use App\Events\MessagesRead;
use App\Events\PrivateMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\PrivateMessageResource;
use App\Jobs\ProcessMessageNotification;
use App\Models\Conversation;
use App\Models\PrivateMessage;
use App\Models\User;
use App\Notifications\NewPrivateMessageNotification;
use DB;
use Illuminate\Http\Request;
use Storage;

class ConversationController extends Controller
{
    /**
     * جلب كل المحادثات الخاصة بالمستخدم الحالي
     */

    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = $user->conversations()->withDetailsForUser($user)->get();

        return ConversationResource::collection($conversations);
    }
    /**
     * جلب كل الرسائل في محادثة معينة
     */
    public function getMessages(Request $request, Conversation $conversation)
    {
        // تأكد من أن المستخدم الحالي هو جزء من هذه المحادثة
        $this->authorize('view', $conversation);

        $query = $conversation->messages()
            ->with(['sender' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'profile_picture');
            }])
            ->latest();

        // إذا قام التطبيق بإرسال 'before_id'، اجلب الرسائل الأقدم فقط
        if ($request->has('before_id')) {
            $query->where('id', '<', $request->input('before_id'));
        }

        // إذا قام التطبيق بإرسال 'after_id'، اجلب الرسائل الأحدث فقط
        if ($request->has('after_id')) {
            $query->where('id', '>', $request->input('after_id'));
        }

        // جلب عدد الرسائل المطلوب (افتراضي 50)
        $limit = min($request->input('limit', 50), 100); // حد أقصى 100 رسالة
        $messages = $query->limit($limit)->get();

        // تحديث آخر قراءة للمستخدم
        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return PrivateMessageResource::collection($messages);
    }

    public function sendMessageToUser(Request $request, User $recipient)
    {
        $validated = $request->validate([
            'content' => 'required_without:attachment|nullable|string|max:10000',
            'type' => 'required|string|in:text,image,video,audio,file',
            'attachment' => 'required_if:type,image,video,audio,file|nullable|file|max:20480',
        ]);

        $currentUser = $request->user();

        if ($currentUser->id === $recipient->id) {
            return response()->json(['message' => 'You cannot send a message to yourself.'], 422);
        }

        // تحسين الاستعلام باستخدام cache
        $conversation = cache()->remember(
            "conversation_{$currentUser->id}_{$recipient->id}",
            300, // 5 دقائق
            function () use ($currentUser, $recipient) {
                return Conversation::query()
                    ->whereHas('participants', fn($q) => $q->where('user_id', $currentUser->id))
                    ->whereHas('participants', fn($q) => $q->where('user_id', $recipient->id))
                    ->whereHas('participants', null, '=', 2)
                    ->first();
            }
        );

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->participants()->attach([$currentUser->id, $recipient->id]);
            
            // تحديث cache
            cache()->put("conversation_{$currentUser->id}_{$recipient->id}", $conversation, 300);
        }

        $messageContent = $validated['content'] ?? null;

        // معالجة الملفات المرفقة
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            
            // التحقق من نوع الملف
            $allowedTypes = [
                'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'video' => ['mp4', 'avi', 'mov', 'wmv'],
                'audio' => ['mp3', 'wav', 'ogg', 'aac'],
                'file' => ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar']
            ];
            
            $fileExtension = strtolower($file->getClientOriginalExtension());
            if (!in_array($fileExtension, $allowedTypes[$validated['type']] ?? [])) {
                return response()->json(['message' => 'Invalid file type for this message type.'], 422);
            }
            
            $path = $file->store('attachments', 'public');
            $messageContent = $path;
        }

        // إنشاء الرسالة
        $message = $conversation->messages()->create([
            'sender_id' => $currentUser->id,
            'content' => $messageContent,
            'type' => $validated['type'],
            'is_read' => false,
        ]);

        $message->load('sender');

        // بث الأحداث بشكل متوازي لتحسين الأداء
        try {
            // بث حدث الرسالة الجديدة
            broadcast(new PrivateMessageSent($message))->toOthers();

            // بث حدث تحديث المحادثة
            $recipientUser = $conversation->participants->where('id', '!=', $currentUser->id)->first();
            if ($recipientUser) {
                broadcast(new ConversationUpdated($conversation, $recipientUser))->toOthers();
                
                // إرسال الإشعار في الخلفية باستخدام Job
                ProcessMessageNotification::dispatch($message, $recipientUser->id);
            }
        } catch (\Exception $e) {
            // تسجيل الخطأ ولكن لا نوقف العملية
            \Log::error('Broadcasting error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => new PrivateMessageResource($message),
            'conversation_id' => $conversation->id,
        ], 201);
    }
    /**
     * حذف محادثة معينة.
     */
    public function destroy(Request $request, Conversation $conversation)
    {
        // 1. التحقق الأمني: تأكد من أن المستخدم الحالي هو أحد المشاركين في المحادثة
        $this->authorize('view', $conversation);

        // 2. قم بحذف المحادثة
        // سيقوم onDelete('cascade') في قاعدة البيانات بحذف كل الرسائل المرتبطة تلقائيًا
        $conversation->delete();

        // 3. أعد رسالة نجاح
        return response()->json(['message' => 'Conversation deleted successfully.']);
    }
    /**
     * Delete a specific message.
     */
    public function destroyMessage(Request $request, PrivateMessage $message)
    {
        // 1. Security Check: Use the policy to ensure only the sender can delete.
        // This will automatically return a 403 Forbidden error if the check fails.
        $this->authorize('delete', $message);

        // 2. If the message was a file, delete the file from storage.
        if ($message->type !== 'text' && $message->content) {
            Storage::disk('public')->delete($message->content);
        }

        // 3. Delete the message record from the database.
        $message->delete();

        // (Optional but recommended) Broadcast an event so the message disappears in real-time.
        // broadcast(new MessageDeleted($message->id, $message->conversation_id))->toOthers();

        // 4. Return a success response.
        return response()->json(['message' => 'Message deleted successfully.']);
    }

    public function markAsRead(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation); // Ensure the user is a participant

        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return response()->json(['message' => 'Conversation marked as read.']);
    }
    /**
     * Mark specific messages as read (for the "seen" checkmarks).
     */
    public function markMessagesAsRead(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $user = $request->user();

        // استخدام batch update لتحسين الأداء
        $updatedCount = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($updatedCount > 0) {
            // جلب IDs الرسائل المحدثة للبث
            $messageIds = $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', true)
                ->where('updated_at', '>=', now()->subSeconds(5)) // الرسائل المحدثة في آخر 5 ثواني
                ->pluck('id')
                ->toArray();

            if (!empty($messageIds)) {
                // بث الحدث في الخلفية
                dispatch(function () use ($messageIds, $conversation) {
                    broadcast(new MessagesRead($messageIds, $conversation->id))->toOthers();
                })->afterResponse();
            }
        }

        // تحديث آخر قراءة في جدول المشاركين
        $conversation->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        return response()->json([
            'message' => 'Messages marked as read.',
            'updated_count' => $updatedCount
        ]);
    }
}
