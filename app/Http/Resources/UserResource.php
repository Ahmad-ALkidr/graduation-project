<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'profile_picture' => $this->profile_picture ? asset('storage/' . $this->profile_picture) : null,
            'posts_count' => $this->posts_count,
            'comments_count' => $this->comments_count,
            'likes_count' => $this->likes_count,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'university_id' => $this->university_id,
            'college' => $this->college,
            'major' => $this->major,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
