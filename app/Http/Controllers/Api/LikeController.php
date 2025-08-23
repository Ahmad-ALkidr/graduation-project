<?php

namespace App\Http\Controllers\Api;

use App\Events\PostLikesUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Notifications\NewLikeNotification;
// use App\Notifications\PostLikedNotification;

class LikeController extends Controller
{
    /**
     * Handle the action of liking or unliking a post.
     */
// in LikeController.php

public function toggleLike(Request $request, Post $post)
{
    $user = $request->user();

    $result = $user->likedPosts()->toggle($post->id);

    $isLiked = count($result['attached']) > 0;

    $post->likes_count = $post->likers()->count();
    $post->save();

    PostLikesUpdated::dispatch($post->id, $post->likes_count);

    if ($isLiked && $post->user_id !== $user->id) {
        $post->user->notify(new NewLikeNotification($user, $post));
    }

    // 🔹 تحديث is_liked_by_user يدوياً
    $post->is_liked_by_user = $user ? $post->likers()->where('user_id', $user->id)->exists() : false;

    $post->load('user')->loadCount('likers', 'comments');

    return new PostResource($post);
}


}
