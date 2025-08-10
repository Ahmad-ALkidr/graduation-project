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
    public function search(Request $request)
    {
        // 1. التحقق من أن حقل البحث موجود ويحتوي على حرفين على الأقل
        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $searchQuery = $validated['query'];

        // 2. البحث في قاعدة البيانات
        $users = User::where(function ($query) use ($searchQuery) {
                // البحث في الاسم الأول أو اسم العائلة
                $query->where('first_name', 'LIKE', "{$searchQuery}%")
                      ->orWhere('last_name', 'LIKE', "{$searchQuery}%")
                      // يمكنك إضافة البحث في الاسم الكامل إذا أردت
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "{$searchQuery}%");
            })
            ->where('id', '!=', auth()->id()) // 3. استثناء المستخدم الحالي من نتائج البحث
            ->limit(10) // 4. تحديد عدد النتائج بـ 10 كحد أقصى لتحسين الأداء
            ->get();

        // 5. إرجاع النتائج باستخدام الـ Resource الذي أنشأناه سابقًا
        // هذا يضمن عدم تسريب أي بيانات خاصة
        return PublicProfileResource::collection($users);
    }
}
