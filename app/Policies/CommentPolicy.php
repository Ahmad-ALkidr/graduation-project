<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        // اسمح للمستخدم بتعديل التعليق فقط إذا كان هو كاتبه
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // اسمح بحذف التعليق إذا كان المستخدم هو كاتبه،
        // أو إذا كان المستخدم هو صاحب المنشور الأصلي
        return $user->id === $comment->user_id || $user->id === $comment->post->user_id;
    }
}
