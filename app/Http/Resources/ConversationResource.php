<?php
// app/Http/Resources/ConversationResource.php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
        /**
     * The user who is "viewing" the resource.
     *
     * @var \App\Models\User|null
     */
    protected ?User $viewer = null;

    /**
     * Set the user who is viewing the resource.
     *
     * @param \App\Models\User $user
     * @return $this
     */
    public function forViewer(User $user): self
    {
        $this->viewer = $user;
        return $this;
    }
    public function toArray(Request $request): array
    {
        $viewerId = $this->viewer ? $this->viewer->id : auth()->id();
        // ابحث عن المشارك الآخر في المحادثة
        $otherParticipant = $this->participants->where('id', '!=', $viewerId)->first();
        if (!$otherParticipant) {
            return [];
        }
        // If the other participant exists, return their data as normal.
        return [
            'id' => $this->id,
            // معلومات الشخص الآخر
            'participant' => [
                'id' => $otherParticipant->id,
                'name' => $otherParticipant->first_name . ' ' . $otherParticipant->last_name,
                'profile_picture_url' => $otherParticipant->profile_picture_url,
            ],
            // آخر رسالة في المحادثة
            'last_message' => new PrivateMessageResource($this->whenLoaded('latestMessage')),
            'unread_count' => $this->unread_count, // ✨ Add this line
            'updated_at' => $this->updated_at,
        ];
    }
}
