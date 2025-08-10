<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'created_at' => $this->created_at->diffForHumans(), // تنسيق الوقت ليكون أسهل في القراءة

            'likes_count' => $this->whenCounted('likers', $this->likers_count, 0), // عرض عدد الإعجابات
            'comments_count' => $this->whenCounted('comments', $this->comments_count, 0), // عرض عدد التعليقات

            'is_liked_by_user' => $this->when(auth()->check(), function () {
                // هل المستخدم الحالي معجب بهذا المنشور؟
                return $this->likers()->where('user_id', auth()->id())->exists();
            }),

            'author' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
