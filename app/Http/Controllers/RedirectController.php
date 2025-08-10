<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /**
     * إعادة توجيه طلب إعادة تعيين كلمة المرور إلى التطبيق
     */
    public function redirectToApp(Request $request)
    {
        // 1. استخراج التوكن والإيميل من الرابط
        $token = $request->query('token');
        $email = $request->query('email');

        // 2. بناء الرابط العميق الخاص بالتطبيق
        $deepLinkUrl = "shamunity://reset-password?token={$token}&email=" . urlencode($email);

        // 3. إرجاع رد إعادة توجيه إلى الرابط العميق
        return redirect()->away($deepLinkUrl);
    }
}
