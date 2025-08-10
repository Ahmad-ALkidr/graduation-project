<?php
// app/Http/Resources/PublicProfileResource.php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            // استخدم الـ Accessor الجديد الذي أنشأناه في موديل User
            'profile_picture_url' => $this->profile_picture_url,
            'role' => $this->role,
        ];

        if ($this->role === RoleEnum::STUDENT) {
            $data = array_merge($data, [
                'college' => $this->college,
                'major' => $this->major,
                'year' => $this->year,
            ]);
        } elseif ($this->role === RoleEnum::ACADEMIC) {
            $data = array_merge($data, [
                'subjects' => $this->subjects()->pluck('name'),
            ]);
        }

        return $data;
    }
}
