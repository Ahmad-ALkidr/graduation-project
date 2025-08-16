<?php

namespace App\Http\Controllers\Api;

use App\Events\ConversationUpdated;
use App\Events\PrivateMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\PrivateMessageResource;
use App\Models\Conversation;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * جلب كل المحادثات الخاصة بالمستخدم الحالي
     */
    // في ConversationController.php

    // in ConversationController.php

public function index(Request $request)
    {
        $user = $request->user();

        $conversations = $user->conversations()
            ->with([
                'participants' => fn($query) => $query->where('user_id', '!=', $user->id),
                'latestMessage.sender'
            ])
            ->addSelect([
                'unread_count' => DB::table('private_messages as pm')
                    ->selectRaw('count(*)')
                    ->whereColumn('pm.conversation_id', 'conversations.id')
                    ->where('pm.sender_id', '!=', $user->id)
                    ->where(function ($query) use ($user) {

                        $lastReadQuery = DB::table('conversation_user as cu')
                            ->select('cu.last_read_at')
                            ->where('cu.user_id', $user->id)
                            ->whereColumn('cu.conversation_id', 'conversations.id');

                        // ✨ --- This is the final corrected logic block --- ✨

                        // We convert the subquery to a raw SQL string and get its bindings
                        $subQuerySql = $lastReadQuery->toSql();
                        $subQueryBindings = $lastReadQuery->getBindings();

                        // Now we use the raw SQL string in our conditions
                        $query->whereRaw("pm.created_at > ({$subQuerySql})", $subQueryBindings)
                              ->orWhereRaw("({$subQuerySql}) IS NULL", $subQueryBindings);
                    })
            ])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->get();

        return ConversationResource::collection($conversations);
    }
    /**
     * بدء محادثة جديدة أو جلب محادثة موجودة
     */
    // public function store(Request $request)
    // {
    //     $validated = $request->validate(['user_id' => 'required|integer|exists:users,id']);
    //     $otherUserId = $validated['user_id'];
    //     $currentUser = $request->user();

    //     // ابحث عن محادثة موجودة بالفعل بين هذين المستخدمين
    //     $conversation = $currentUser->conversations()
    //         ->whereHas('participants', function ($query) use ($otherUserId) {
    //             $query->where('user_id', $otherUserId);
    //         })
    //         ->first();

    //     // إذا لم توجد محادثة، قم بإنشاء واحدة جديدة
    //     if (!$conversation) {
    //         $conversation = Conversation::create();
    //         $conversation->participants()->attach([$currentUser->id, $otherUserId]);
    //     }

    //     return new ConversationResource($conversation->load('participants'));
    // }

    /**
     * جلب كل الرسائل في محادثة معينة
     */
    public function getMessages(Request $request, Conversation $conversation)
    {
        // تأكد من أن المستخدم الحالي هو جزء من هذه المحادثة
        $this->authorize('view', $conversation);

        $query = $conversation->messages()->with('sender')->latest();

        // إذا قام التطبيق بإرسال 'before_id'، اجلب الرسائل الأقدم فقط
        if ($request->has('before_id')) {
            $query->where('id', '<', $request->input('before_id'));
        }

        // جلب 50 رسالة فقط في كل مرة
        $messages = $query->limit(50)->get();

        // هذا الرد سيعيد فقط مصفوفة بالرسائل داخل مفتاح "data"
        return PrivateMessageResource::collection($messages);
    }

    /**
     * إرسال رسالة جديدة في محادثة
     */
    // public function sendMessage(Request $request, Conversation $conversation)
    // {
    //     $this->authorize('view', $conversation);

    //     $validated = $request->validate(['content' => 'required|string']);

    //     $message = $conversation->messages()->create([
    //         'sender_id' => $request->user()->id,
    //         'content' => $validated['content'],
    //     ]);

    //     $message->load('sender');
    //     $conversation->load('participants', 'latestMessage.sender');

    //     // بث الرسالة الجديدة للمشاركين الآخرين
    //     broadcast(new PrivateMessageSent($message))->toOthers();
    //     broadcast(new ConversationUpdated($conversation));

    //     return new PrivateMessageResource($message);
    // }
    public function sendMessageToUser(Request $request, User $recipient)
    {
        $validated = $request->validate(['content' => 'required|string|max:10000']);
        $currentUser = $request->user();

        // منع المستخدم من مراسلة نفسه
        if ($currentUser->id === $recipient->id) {
            return response()->json(['message' => 'لا يمكنك إرسال رسالة لنفسك.'], 422);
        }

        // ابحث عن محادثة موجودة بين هذين المستخدمين فقط
        $conversation = Conversation::whereHas('participants', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })
            ->whereHas('participants', function ($query) use ($recipient) {
                $query->where('user_id', $recipient->id);
            })
            ->whereHas('participants', null, '=', 2) // تأكد من أنها تحتوي على مشاركين اثنين فقط
            ->first();

        // إذا لم توجد محادثة، قم بإنشاء واحدة جديدة
        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->participants()->attach([$currentUser->id, $recipient->id]);
        }

        // الآن، قم بإنشاء الرسالة داخل هذه المحادثة
        $message = $conversation->messages()->create([
            'sender_id' => $currentUser->id,
            'content' => $validated['content'],
        ]);

        $message->load('sender');

        // بث الرسالة الجديدة لحظيًا إلى قناة المحادثة
        broadcast(new PrivateMessageSent($message))->toOthers();

        // بث تحديث لقائمة محادثات كلا المستخدمين
        broadcast(new ConversationUpdated($conversation));

        // إرجاع الرسالة الجديدة ومعرّف المحادثة
        return response()->json(
            [
                'message' => new PrivateMessageResource($message),
                'conversation_id' => $conversation->id,
            ],
            201,
        );
    }
    public function addMessageToConversion(Request $request, User $recipient)
    {
        $validated = $request->validate(['content' => 'required|string|max:10000']);
        $currentUser = $request->user();

        // منع المستخدم من مراسلة نفسه
        if ($currentUser->id === $recipient->id) {
            return response()->json(['message' => 'لا يمكنك إرسال رسالة لنفسك.'], 422);
        }

        $conversation = Conversation::create();
        $conversation->participants()->attach([$currentUser->id, $recipient->id]);

        // الآن، قم بإنشاء الرسالة داخل هذه المحادثة
        $message = $conversation->messages()->create([
            'sender_id' => $currentUser->id,
            'content' => $validated['content'],
        ]);

        $message->load('sender');

        // بث الرسالة الجديدة لحظيًا إلى قناة المحادثة
        broadcast(new PrivateMessageSent($message))->toOthers();

        // بث تحديث لقائمة محادثات كلا المستخدمين
        broadcast(new ConversationUpdated($conversation));

        // إرجاع الرسالة الجديدة ومعرّف المحادثة
        return response()->json(
            [
                'message' => new PrivateMessageResource($message),
                'conversation_id' => $conversation->id,
            ],
            201,
        );
    }
    public function sendMessageToConversation(Request $request, Conversation $conversation)
    {
        // 1. التحقق من الصلاحية: هل المستخدم الحالي عضو في هذه المحادثة؟
        // هذا السطر هو أهم سطر أمني في الدالة لمنع أي شخص من إرسال
        // رسائل لمحادثات لا يشارك فيها.
        $this->authorize('view', $conversation);

        // 2. التحقق من مدخلات الطلب
        $validated = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        // 3. إنشاء الرسالة الجديدة
        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        // تحميل بيانات المرسل مع الرسالة لإعادتها في الرد
        $message->load('sender');

        // 4. بث الرسالة لحظيًا للمشاركين الآخرين في المحادثة
        broadcast(new PrivateMessageSent($message))->toOthers();

        // 5. بث تحديث للمحادثة نفسها (لتظهر في أعلى قائمة المحادثات عند الجميع)
        broadcast(new ConversationUpdated($conversation->load('participants', 'latestMessage.sender')));

        // 6. إرجاع بيانات الرسالة الجديدة كاستجابة
        return new PrivateMessageResource($message);
    }

    // in ConversationController.php

    /**
     * Mark a conversation as read for the current user.
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation); // Ensure the user is a participant

        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return response()->json(['message' => 'Conversation marked as read.']);
    }
}
