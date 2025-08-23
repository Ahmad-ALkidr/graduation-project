<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendPasswordResetOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Ichtrojan\Otp\Otp;

class PasswordResetController extends Controller
{
    private $otpService;

    public function __construct()
    {
        $this->otpService = new Otp;
    }

    /**
     * الخطوة 1: إرسال رمز OTP إلى البريد الإلكتروني
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $request->email)->first();

        $otp = $this->otpService->generate($request->email, 'numeric', 6, 15);
        $user->notify(new SendPasswordResetOtp($otp->token));

        return response()->json(['message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.']);
    }

    /**
     * الخطوة 2: التحقق من الـ OTP وإصدار توكن إعادة التعيين
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp_code' => 'required|string|size:6',
        ]);

        $otpValidation = $this->otpService->validate($request->email, $request->otp_code);

        if (!$otpValidation->status) {
            return response()->json(['message' => $otpValidation->message], 400);
        }

        // --- التعديل الرئيسي هنا ---
        // 1. حذف أي توكنات قديمة لهذا المستخدم
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // 2. إنشاء توكن إعادة تعيين جديد وآمن
        $resetToken = Str::random(60);

        // 3. حفظ التوكن في قاعدة البيانات
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $resetToken, // سنقوم بحفظه كنص عادي لسهولة المقارنة
            'created_at' => now(),
        ]);

        // 4. إرجاع التوكن للتطبيق ليستخدمه في الخطوة التالية
        return response()->json([
            'message' => 'الرمز صحيح.',
            'reset_token' => $resetToken
        ]);
    }

    /**
     * الخطوة 3: إعادة تعيين كلمة المرور باستخدام التوكن
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // 1. التحقق من صحة توكن إعادة التعيين
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->reset_token)
            ->first();

        // تحقق من أن التوكن موجود وصالح (لم يمر عليه أكثر من 15 دقيقة مثلاً)
        if (!$tokenRecord || now()->subMinutes(15)->gt($tokenRecord->created_at)) {
            return response()->json(['message' => 'توكن إعادة التعيين غير صالح أو منتهي الصلاحية.'], 400);
        }

        // 2. تحديث كلمة المرور
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح.']);
    }
}
