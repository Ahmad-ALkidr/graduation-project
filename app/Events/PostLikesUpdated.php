<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLikesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $postId;
    public int $likesCount;

    /**
     * Create a new event instance.
     */
    public function __construct(int $postId, int $likesCount)
    {
        $this->postId = $postId;
        $this->likesCount = $likesCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // نرسل التحديث على القناة الخاصة بالمنشور المحدد
        return [new PrivateChannel('posts.' . $this->postId)];
    }

    public function broadcastAs(): string
    {
        return 'post.likes.updated';
    }

    public function broadcastWith(): array
    {
        // نرسل فقط البيانات التي تحتاجها الواجهة الأمامية لتحديث نفسها
        return [
            'id' => $this->postId,
            'likes_count' => $this->likesCount,
        ];
    }
}
