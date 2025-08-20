<?php

namespace App\Http\Controllers\Api;

use App\Events\ConversationUpdated;
use App\Events\MessageDeleted;
use App\Events\MessagesRead;
use App\Events\PrivateMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\PrivateMessageResource;
use App\Jobs\ProcessMessageAttachment;
use App\Jobs\ProcessMessageNotification;
use App\Models\Conversation;
use App\Models\PrivateMessage;
use App\Models\User;
use App\Notifications\NewPrivateMessageNotification;
use DB;
use Illuminate\Http\Request;
use Notification;
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

        $query = $conversation
            ->messages()
            ->with([
                'sender' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'profile_picture');
                },
            ])
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

    // public function sendMessageToUser(Request $request, User $recipient)
    // {
    //     $validated = $request->validate([
    //         'content' => 'required_without:attachment|nullable|string|max:10000',
    //         'type' => 'required|string|in:text,image,video,audio,file',
    //         'attachment' => 'required_if:type,image,video,audio,file|nullable|file|max:20480',
    //     ]);

    //     $currentUser = $request->user();

    //     if ($currentUser->id === $recipient->id) {
    //         return response()->json(['message' => 'You cannot send a message to yourself.'], 422);
    //     }

    //     // تحسين الاستعلام باستخدام cache
    //     $conversation = cache()->remember(
    //         "conversation_{$currentUser->id}_{$recipient->id}",
    //         300, // 5 دقائق
    //         function () use ($currentUser, $recipient) {
    //             return Conversation::query()
    //                 ->whereHas('participants', fn($q) => $q->where('user_id', $currentUser->id))
    //                 ->whereHas('participants', fn($q) => $q->where('user_id', $recipient->id))
    //                 ->whereHas('participants', null, '=', 2)
    //                 ->first();
    //         }
    //     );

    //     if (!$conversation) {
    //         $conversation = Conversation::create();
    //         $conversation->participants()->attach([$currentUser->id, $recipient->id]);

    //         // تحديث cache
    //         cache()->put("conversation_{$currentUser->id}_{$recipient->id}", $conversation, 300);
    //     }

    //     $messageContent = $validated['content'] ?? null;

    //     // معالجة الملفات المرفقة
    //     if ($request->hasFile('attachment')) {
    //         $file = $request->file('attachment');

    //         // التحقق من نوع الملف
    //         $allowedTypes = [
    //             'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    //             'video' => ['mp4', 'avi', 'mov', 'wmv'],
    //             'audio' => ['mp3', 'wav', 'ogg', 'aac'],
    //             'file' => ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar']
    //         ];

    //         $fileExtension = strtolower($file->getClientOriginalExtension());
    //         if (!in_array($fileExtension, $allowedTypes[$validated['type']] ?? [])) {
    //             return response()->json(['message' => 'Invalid file type for this message type.'], 422);
    //         }

    //         $path = $file->store('attachments', 'public');
    //         $messageContent = $path;
    //     }

    //     // إنشاء الرسالة
    //     $message = $conversation->messages()->create([
    //         'sender_id' => $currentUser->id,
    //         'content' => $messageContent,
    //         'type' => $validated['type'],
    //         'is_read' => false,
    //     ]);

    //     $message->load('sender');

    //     // المهام الفورية (تتم فوراً)
    //     try {
    //         // بث حدث الرسالة الجديدة
    //         broadcast(new PrivateMessageSent($message))->toOthers();

    //         // بث حدث تحديث المحادثة
    //         $recipientUser = $conversation->participants->where('id', '!=', $currentUser->id)->first();
    //         if ($recipientUser) {
    //             broadcast(new ConversationUpdated($conversation, $recipientUser))->toOthers();
    //         }
    //     } catch (\Exception $e) {
    //         \Log::error('Broadcasting error: ' . $e->getMessage());
    //     }

    //     // المهام البطيئة (تتم في الخلفية)
    //     if ($recipientUser) {
    //         // إرسال الإشعارات
    //         ProcessMessageNotification::dispatch($message, $recipientUser->id);

    //         // معالجة الملفات المرفقة
    //         if ($validated['type'] !== 'text') {
    //             ProcessMessageAttachment::dispatch($message);
    //         }
    //     }

    //     return response()->json([
    //         'message' => new PrivateMessageResource($message),
    //         'conversation_id' => $conversation->id,
    //     ], 201);
    // }
    /**
     * حذف محادثة معينة.
     */
    // in ConversationController.php

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

        // ✨ --- THIS IS THE FINAL, OPTIMIZED QUERY --- ✨
        // This finds the conversation and eager loads the recipient at the same time.
        $conversation = $currentUser
            ->conversations()
            ->whereHas('participants', fn($q) => $q->where('user_id', $recipient->id))
            ->whereHas('participants', null, '=', 2)
            ->with(['participants' => fn($q) => $q->where('user_id', '!=', $currentUser->id)])
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->participants()->attach([$currentUser->id, $recipient->id]);
            // Since it's a new conversation, the recipient is the one we already have
        } else {
            // If the conversation exists, the recipient is the one we loaded
            $recipient = $conversation->participants->first();
        }

        $messageContent = $validated['content'] ?? null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
            $messageContent = $path;
        }

        $message = $conversation->messages()->create([
            'sender_id' => $currentUser->id,
            'content' => $messageContent,
            'type' => $validated['type'],
        ]);
        $message->load('sender');

        // Broadcast the events
        broadcast(new PrivateMessageSent($message))->toOthers();
        broadcast(new ConversationUpdated($conversation, $recipient))->toOthers();
        $recipient->notify(new NewPrivateMessageNotification($message));

        return response()->json(
            [
                'message' => new PrivateMessageResource($message),
            ],
            201,
        );
    }
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
        // Keep a copy of the IDs before deleting
        $messageId = $message->id;
        $conversationId = $message->conversation_id;
        // 2. If the message was a file, delete the file from storage.
        if ($message->type !== 'text' && $message->content) {
            Storage::disk('public')->delete($message->content);
        }

        // 3. Delete the message record from the database.
        $message->delete();

        //✨ Broadcast the event so the message disappears in real-time for other users✨
        broadcast(new MessageDeleted($messageId, $conversationId));

        // 4. Return a success response.
        return response()->json([
            'message' => 'Message deleted successfully.',
        ]);
    }

    // public function markAsRead(Request $request, Conversation $conversation)
    // {
    //     $this->authorize('view', $conversation); // Ensure the user is a participant

    //     $conversation->participants()->updateExistingPivot($request->user()->id, [
    //         'last_read_at' => now(),
    //     ]);

    //     return response()->json(['message' => 'Conversation marked as read.']);
    // }
    // /**
    //  * Mark specific messages as read (for the "seen" checkmarks).
    //  */
    // public function markMessagesAsRead(Request $request, Conversation $conversation)
    // {
    //     $this->authorize('view', $conversation);

    //     $user = $request->user();

    //     // استخدام batch update لتحسين الأداء
    //     $updatedCount = $conversation
    //         ->messages()
    //         ->where('sender_id', '!=', $user->id)
    //         ->where('is_read', false)
    //         ->update(['is_read' => true]);

    //     if ($updatedCount > 0) {
    //         // جلب IDs الرسائل المحدثة للبث
    //         $messageIds = $conversation
    //             ->messages()
    //             ->where('sender_id', '!=', $user->id)
    //             ->where('is_read', true)
    //             ->where('updated_at', '>=', now()->subSeconds(5)) // الرسائل المحدثة في آخر 5 ثواني
    //             ->pluck('id')
    //             ->toArray();

    //         if (!empty($messageIds)) {
    //             // بث الحدث في الخلفية
    //             dispatch(function () use ($messageIds, $conversation) {
    //                 broadcast(new MessagesRead($messageIds, $conversation->id))->toOthers();
    //             })->afterResponse();
    //         }
    //     }

    //     // تحديث آخر قراءة في جدول المشاركين
    //     $conversation->participants()->updateExistingPivot($user->id, [
    //         'last_read_at' => now(),
    //     ]);

    //     return response()->json([
    //         'message' => 'Messages marked as read.',
    //         'updated_count' => $updatedCount,
    //     ]);
    // }
    /**
     * Find the conversation ID for a given recipient.
     * Returns the ID if a conversation exists, otherwise returns null.
     */
    /**
     * Marks a conversation as read, updates individual messages for "seen" status,
     * and broadcasts the read receipts, all in one action.
     */
    public function markConversationAsRead(Request $request, Conversation $conversation)
    {
        // 1. Security Check: Ensure the user is a participant.
        $this->authorize('view', $conversation);
        $user = $request->user();

        // 2. Update the 'last_read_at' timestamp for the UNREAD COUNT in the conversation list.
        // This is the first function's logic.
        $conversation->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        // 3. Find all messages sent by the OTHER user that are not yet "seen".
        $unreadMessages = $conversation->messages()->where('sender_id', '!=', $user->id)->where('is_read', false);

        // 4. If there are any, update them and notify the sender.
        if ($unreadMessages->exists()) {
            $messageIdsToUpdate = $unreadMessages->pluck('id')->toArray();

            // Mark all found messages as read (for the "seen" checkmarks)
            $unreadMessages->update(['is_read' => true]);

            // Broadcast the event with the IDs of the messages that were just read
            broadcast(new MessagesRead($messageIdsToUpdate, $conversation->id));
        }

        return response()->json(['message' => 'Conversation marked as read.']);
    }
    public function findConversationWithUser(Request $request, User $recipient)
    {
        $currentUser = $request->user();

        // Find the one-on-one conversation between the current user and the recipient
        $conversation = Conversation::query()
            ->whereHas('participants', fn($q) => $q->where('user_id', $currentUser->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $recipient->id))
            ->whereHas('participants', null, '=', 2) // Ensures it's only a 1-on-1 chat
            ->first();

        // Return a JSON response with the conversation ID, which will be null if not found
        return response()->json([
            'conversation_id' => $conversation ? $conversation->id : null,
        ]);
    }
}
