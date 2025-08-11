<?php
// app/Events/CommentUpdated.php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel; // <-- تم التغيير هنا
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Comment $comment)
    {
        $this->comment->load('user');
    }

    public function broadcastOn(): array
    {
        // نستخدم قناة عامة لسهولة الوصول
        return [new Channel('post.' . $this->comment->post_id)];
    }

    public function broadcastAs(): string
    {
        return 'comment.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id'       => $this->comment->id,
            'content'  => $this->comment->content,
            'created_at' => $this->comment->created_at->diffForHumans(),
            'author'   => [
                'id'   => $this->comment->user->id,
                'name' => $this->comment->user->first_name . ' ' . $this->comment->user->last_name,
            ]
        ];
    }
}
