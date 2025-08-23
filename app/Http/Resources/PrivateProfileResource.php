<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivateProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // ابدأ بالبيانات العامة
        $data = (new PublicProfileResource($this))->toArray($request);

        // أضف البيانات الخاصة التي يراها المالك فقط
        $privateData = [
            'email' => $this->email,
            'status' => $this->status,
        ];

        if ($this->role === RoleEnum::STUDENT) {
            $privateData = array_merge($privateData, [
                'gender' => $this->gender,
                'birth_date' => $this->birth_date,
                'university_id' => $this->university_id,
                // إحصائيات
                'approved_files_count' => $this->bookRequests()->where('status', 'approved')->count(),
                'pending_files_count' => $this->bookRequests()->where('status', 'pending')->count(),
            ]);
        }

        return array_merge($data, $privateData);
    }
}
