<?php

namespace App\Events;

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Conversation $conversation;
    public User $recipient;

    public function __construct(Conversation $conversation, User $recipient)
    {
        $this->conversation = $conversation;
        $this->recipient = $recipient;
    }

    public function broadcastOn(): array
    {
        // سنقوم ببث هذا الحدث على القناة الشخصية لكل مشارك في المحادثة
        $channels = [];
        foreach ($this->conversation->participants as $participant) {
            $channels[] = new PrivateChannel('App.Models.User.' . $participant->id);
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    public function broadcastWith(): array
    {
        // Re-fetch a "fresh" copy of the conversation for the recipient
        // at the exact moment of broadcasting.
        $freshConversation = Conversation::where('id', $this->conversation->id)
            ->withDetailsForUser($this->recipient) // Using the scope
            ->first();
        // If for some reason it's not found, fall back to the original
        $conversationToSend = $freshConversation ?: $this->conversation;
        // إرسال بيانات المحادثة المحدثة (مع آخر رسالة)
        return ['conversation' => new ConversationResource($conversationToSend)];
    }
}
