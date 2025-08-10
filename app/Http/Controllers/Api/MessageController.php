<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\ChatGroup;
use App\Notifications\NewMessageInGroupNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class MessageController extends Controller
{
    public function index(Request $request, ChatGroup $group)
    {
        $this->authorize('view', $group);
        $messages = $group->messages()->with('user')->latest()->paginate(50);
        return MessageResource::collection($messages);
    }

    public function store(Request $request, ChatGroup $group)
    {
        $this->authorize('sendMessage', $group);
        $validated = $request->validate(['content' => 'required|string']);
        $user = $request->user();

        $message = $group->messages()->create([
            'user_id' => $user->id,
            'content' => $validated['content'],
            'type' => 'text',
        ]);

        $message->load('user');

        // إرسال إشعار دفع (Push Notification) لجميع الأعضاء الآخرين
        $otherMembers = $group->members()->where('user_id', '!=', $user->id)->get();
        Notification::send($otherMembers, new NewMessageInGroupNotification($message));

        return new MessageResource($message);
    }
}
