<?php
// app/Events/CommentDeleted.php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel; 
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Comment $comment)
    {
    }

    public function broadcastOn(): array
    {
        // نستخدم قناة عامة لسهولة الوصول
        return [new Channel('post.' . $this->comment->post_id)];
    }

    public function broadcastAs(): string
    {
        return 'comment.deleted';
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->comment->id];
    }
}
