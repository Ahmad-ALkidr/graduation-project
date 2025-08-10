<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // --- هذا هو التعديل الرئيسي ---
        // إذا كان الطلب هو طلب API (يتوقع استجابة JSON)،
        // لا تقم بإعادة التوجيه، بل أعد القيمة null.
        // سيقوم Laravel تلقائيًا بإرسال استجابة 401 Unauthorized JSON.
        return $request->expectsJson() ? null : route('login');
    }
}
