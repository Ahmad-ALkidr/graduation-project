<?php
// app/Events/PrivateMessageSent.php

namespace App\Events;

use App\Http\Resources\PrivateMessageResource;
use App\Models\PrivateMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PrivateMessage $message;

    public function __construct(PrivateMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        // سنقوم ببث الرسالة على قناة خاصة بالمحادثة
        return [
            new PrivateChannel('chat.private.' . $this->message->conversation_id),
        ];
    }

    public function broadcastWith(): array
    {
        return ['message' => new PrivateMessageResource($this->message)];
    }
}
