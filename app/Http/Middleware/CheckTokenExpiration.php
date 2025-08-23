<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. تحقق مما إذا كان هناك توكن في الطلب
        if ($token = $request->bearerToken()) {
            // 2. ابحث عن التوكن في قاعدة البيانات
            $accessToken = PersonalAccessToken::findToken($token);

            // 3. إذا وجدنا التوكن وتحققنا من أنه منتهي الصلاحية
            if ($accessToken && $accessToken->expires_at && $accessToken->expires_at->isPast()) {
                // 4. أعد رسالة ورمز خطأ فريدين
                return response()->json([
                    'message' => 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مجدداً.',
                    'error_code' => 'TOKEN_EXPIRED' // رمز فريد للواجهة الأمامية
                ], 419); // 419 Authentication Timeout
            }
        }

        // إذا كان التوكن غير موجود أو صالح، اسمح للطلب بالمرور
        return $next($request);
    }
}
