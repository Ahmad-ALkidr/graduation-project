<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * البحث عن مستخدمين بناءً على الاسم
     */
    public function search($query)
    {
        if (mb_strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $searchQuery = $query;

        $users = User::where(function ($query) use ($searchQuery) {
                $query->where('first_name', 'ILIKE', "{$searchQuery}%")
                      ->orWhere('last_name', 'ILIKE', "{$searchQuery}%")
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'ILIKE', "{$searchQuery}%");
            })
            ->where('id', '!=', auth()->id())
            // نقوم بترتيب النتائج حسب الأولوية
            ->orderByRaw(
                "CASE
                    WHEN first_name ILIKE ? THEN 1
                    WHEN last_name ILIKE ? THEN 2
                    ELSE 3
                END",
                ["{$searchQuery}%", "{$searchQuery}%"]
            )
            ->limit(10)
            ->get();

        return PublicProfileResource::collection($users);
    }
}
