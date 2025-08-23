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

        $users = User::search($query)
            ->where('id', '!=', auth()->id())
            ->orderByRaw(
                "CASE
                    WHEN first_name LIKE ? THEN 1
                    WHEN last_name LIKE ? THEN 2
                    ELSE 3
                END",
                ["{$query}%", "{$query}%"]
            )
            ->limit(10)
            ->get();

        return PublicProfileResource::collection($users);
    }
}
