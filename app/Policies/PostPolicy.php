<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // اسمح للمستخدم بتعديل المنشور فقط إذا كان هو صاحبه
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // اسمح للمستخدم بحذف المنشور فقط إذا كان هو صاحبه
        return $user->id === $post->user_id;
    }
}
