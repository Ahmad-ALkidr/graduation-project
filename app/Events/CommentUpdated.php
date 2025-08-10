<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Comment $comment)
    {
        $this->comment->load('user');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // نرسل التحديث على القناة الخاصة بالمنشور الأب
        return [new PrivateChannel('posts.' . $this->comment->post_id)];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'comment.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // نرسل بيانات التعليق المحدثة كاملة ليتم استبدالها في الواجهة
        return [
            'id'         => $this->comment->id,
            'content'    => $this->comment->content,
            'created_at' => $this->comment->created_at->diffForHumans(),
            'author'     => [ // نستخدم author ليتوافق مع CommentResource
                'id'   => $this->comment->user->id,
                'name' => $this->comment->user->first_name . ' ' . $this->comment->user->last_name,
            ]
        ];
    }
}
