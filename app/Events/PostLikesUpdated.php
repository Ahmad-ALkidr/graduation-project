<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLikesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $postId;
    public int $likesCount;

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
        // سنقوم بالبث على قناة عامة اسمها 'post-likes'
        return [
            new Channel('post-likes'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        // اسم الحدث الذي سيستمع له التطبيق
        return 'like.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->postId,
            'likes_count' => $this->likesCount,
        ];
    }
}
