<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ... other imports

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $messageId;
    public int $conversationId;

    public function __construct(int $messageId, int $conversationId)
    {
        $this->messageId = $messageId;
        $this->conversationId = $conversationId;

    }

    public function broadcastOn(): array
    {
        // Broadcast on the main conversation channel
        return [new PrivateChannel('chat.private.' . $this->conversationId)];
    }

    public function broadcastAs(): string
    {
        // A clear event name for the frontend
        return 'message.deleted';
    }
}
