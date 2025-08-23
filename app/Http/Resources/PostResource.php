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
            'image_url' => $this->when($this->image_path, function() {
                return asset('storage/' . $this->image_path);
            }),
            'created_at' => $this->created_at->diffForHumans(),
            'likes_count' => $this->likers_count ?? 0,
'comments_count' => $this->comments_count ?? 0,
'is_liked_by_user' => (bool) $this->is_liked_by_user,




            'author' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
