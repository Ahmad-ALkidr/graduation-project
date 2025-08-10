<?php

namespace App\Http\Controllers\Api;

use App\Events\PostLikesUpdated;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Notifications\NewLikeNotification;
// use App\Notifications\PostLikedNotification;

class LikeController extends Controller
{
    /**
     * Handle the action of liking or unliking a post.
     */
    public function toggleLike(Request $request, Post $post)
    {
        $user = $request->user();

        $result = $user->likedPosts()->toggle($post->id);

        $isLiked = count($result['attached']) > 0;

        if ($isLiked) {
            // إذا تم إضافة إعجاب، قم بزيادة العداد
            $post->increment('likes_count');
        } else {
           // إذا تم إزالة إعجاب، تحقق أولاً أن العداد أكبر من صفر قبل إنقاصه
            if ($post->likes_count > 0) {
                $post->decrement('likes_count');
            }
            // هذا يمنع العداد من أن يصبح قيمة سالبة
        }

        // (خطوة مهمة) أعد تحميل بيانات النموذج من قاعدة البيانات
        // لضمان أن `likes_count` في الاستجابة هو القيمة المحدثة فعلاً.
        $post->refresh();

        // بث الحدث عبر Pusher لإعلام كل المستخدمين بالتغيير
        PostLikesUpdated::dispatch($post->id, $post->likes_count);

        // (اختياري) إرسال إشعار لصاحب المنشور عند إضافة إعجاب جديد فقط
        if ($isLiked && $post->user_id !== $user->id) {
            $post->user->notify(new NewLikeNotification($user, $post));
        }

        // إرجاع رد فوري للمستخدم الذي ضغط على الزر
        return response()->json([
            'message'     => 'Success',
            'is_liked'    => $isLiked,
            'likes_count' => $post->likes_count,
        ]);
    }
}
