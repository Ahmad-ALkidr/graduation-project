<?php

namespace App\Http\Controllers\Api;

use App\Events\PrivateMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\PrivateMessageResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * جلب كل المحادثات الخاصة بالمستخدم الحالي
     */
    public function index(Request $request)
    {
        $conversations = $request->user()->conversations()
            ->with([
                // جلب المشاركين الآخرين في المحادثة
                'participants' => function ($query) use ($request) {
                    $query->where('user_id', '!=', $request->user()->id);
                },
                // جلب آخر رسالة في كل محادثة
                'latestMessage.sender'
            ])
            ->get();

        return ConversationResource::collection($conversations);
    }

    /**
     * بدء محادثة جديدة أو جلب محادثة موجودة
     */
    public function store(Request $request)
    {
        $validated = $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $otherUserId = $validated['user_id'];
        $currentUser = $request->user();

        // ابحث عن محادثة موجودة بالفعل بين هذين المستخدمين
        $conversation = $currentUser->conversations()
            ->whereHas('participants', function ($query) use ($otherUserId) {
                $query->where('user_id', $otherUserId);
            })
            ->first();

        // إذا لم توجد محادثة، قم بإنشاء واحدة جديدة
        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->participants()->attach([$currentUser->id, $otherUserId]);
        }

        return new ConversationResource($conversation->load('participants'));
    }

    /**
     * جلب كل الرسائل في محادثة معينة
     */
    public function getMessages(Request $request, Conversation $conversation)
    {
        // تأكد من أن المستخدم الحالي هو جزء من هذه المحادثة
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()->with('sender')->latest()->paginate(50);
        return PrivateMessageResource::collection($messages);
    }

    /**
     * إرسال رسالة جديدة في محادثة
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation); // نفس شرط العرض

        $validated = $request->validate(['content' => 'required|string']);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        $message->load('sender');

        // بث الرسالة الجديدة للمشاركين الآخرين
        broadcast(new PrivateMessageSent($message))->toOthers();

        return new PrivateMessageResource($message);
    }
}
