<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $postId)
    {
        // نستقبل فقط الـ ID الخاص بالمنشور المحذوف
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // نرسل الحدث على القناة العامة للمنشورات
        return [new Channel('posts')];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        // اسم واضح للحدث
        return 'post.deleted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // نرسل فقط الـ ID لكي يقوم Flutter بحذف العنصر من القائمة
        return ['id' => $this->postId];
    }
}
