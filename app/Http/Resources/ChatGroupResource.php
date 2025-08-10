<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatGroupResource extends JsonResource
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
            'name' => $this->name,
            'year' => $this->year,
            'is_course_specific' => !is_null($this->course_id), // هل هي مجموعة خاصة بمادة؟
            'members_count' => $this->whenCounted('members'), // عدد الأعضاء
            'is_member' => $this->when(auth()->check(), function () {
                // هل المستخدم الحالي عضو في هذه المجموعة؟
                return auth()->user()->chatGroups()->where('chat_group_id', $this->id)->exists();
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->first_name . ' ' . $this->creator->last_name,
                ];
            }),
        ];
    }
}
