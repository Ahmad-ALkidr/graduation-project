<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FullProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 1. جلب بيانات البروفايل الأساسية باستخدام الـ Resource الموجود
        $profileData = new PublicProfileResource($this->resource);

        // 2. جلب منشورات المستخدم وتنسيقها
        $postsData = PostResource::collection($this->posts()->latest()->get());

        // 3. جلب ملفات المكتبة المعتمدة للمستخدم
        $libraryFilesData = $this->bookRequests()->where('status', 'approved')->latest()->get();

        // 4. دمج كل البيانات في رد واحد منظم
        return [
            'profile' => $profileData,
            'posts' => $postsData,
            'library_files' => $libraryFilesData,
        ];
    }
}
