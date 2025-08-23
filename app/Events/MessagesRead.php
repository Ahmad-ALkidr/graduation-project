<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $messageIds;
    public int $conversationId;

    public function __construct(array $messageIds, int $conversationId)
    {
        $this->messageIds = $messageIds;
        $this->conversationId = $conversationId;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.private.' . $this->conversationId)];
    }

    public function broadcastAs(): string
    {
        return 'messages.read';
    }
}
