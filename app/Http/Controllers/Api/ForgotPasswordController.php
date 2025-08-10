<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /**
     * الخطوة 1: إرسال رابط إعادة تعيين كلمة المرور
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'لا يمكننا العثور على مستخدم بهذا البريد الإلكتروني.'], 404);
        }

        // 3. أنشئ التوكن لهذا المستخدم المحدد
        $token = Password::createToken($user);

        // 4. أرسل إشعار إعادة التعيين لهذا المستخدم
        $user->sendPasswordResetNotification($token);

        return response()->json(['message' => 'تم إرسال رابط إعادة تعيين كلمة المرور بنجاح.']);
    }

    /**
     * الخطوة 2: إعادة تعيين كلمة المرور
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $status = Password::reset($request->all(), function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
        });

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح.']);
        }

        return response()->json(['message' => 'رابط إعادة التعيين هذا غير صالح.'], 400);
    }
}
