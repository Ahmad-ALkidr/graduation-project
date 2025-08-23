<?php
// app/Http/Resources/PrivateMessageResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PrivateMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isText = $this->type === 'text';

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id, // ✨ This is the new line
            'content' => $isText ? $this->content : null,
            'type' => $this->type,
            'is_read' => (bool) $this->is_read, // ✨ Add this line
            'file_url' => !$isText && $this->content ? asset('storage/' . $this->content) : null,
            'created_at' => $this->created_at->toIso8601String(),
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->first_name . ' ' . $this->sender->last_name,
                'profile_picture_url' => $this->sender->profile_picture ? asset('storage/' . $this->sender->profile_picture) : null,
            ],
        ];
    }
}
