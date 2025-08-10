<?php

namespace App\Http\Controllers\Api;

use App\Events\CommentDeleted;
use App\Events\CommentPosted;
use App\Events\CommentUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Requests\Api\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewCommentOnYourPost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    /**
     * عرض كل التعليقات على منشور معين
     */
    public function index(Post $post)
    {
        $comments = $post->comments()->with('user')->latest()->get();
        return CommentResource::collection($comments);
    }

    /**
     * إنشاء تعليق جديد
     */
    public function store(StoreCommentRequest $request, Post $post)
    {
        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->validated('content'),
        ]);

        $comment->load('user');
        CommentPosted::dispatch($comment);

        $postOwner = $post->user;
        if ($postOwner->id !== $comment->user_id) {
            $postOwner->notify(new NewCommentOnYourPost($comment));
        }

        return new CommentResource($comment);
    }

    /**
     * تعديل تعليق موجود
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $comment->update($request->validated());

        CommentUpdated::dispatch($comment->fresh());


        return new CommentResource($comment->load('user'));
    }

    /**
     * حذف تعليق
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        CommentDeleted::dispatch($comment);

        $comment->delete();

        return response()->noContent();
    }
}
