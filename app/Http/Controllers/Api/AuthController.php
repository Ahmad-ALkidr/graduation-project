<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendRegistrationOtp; // <-- نستخدم هذا الإشعار فقط
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Ichtrojan\Otp\Otp;
use Storage;

class AuthController extends Controller
{
    private $otpService;

    public function __construct()
    {
        $this->otpService = new Otp;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'university_id' => 'required|unique:users,university_id',
            'college' => 'required|string',
            'major' => 'required|string',
            'year' => 'required|integer',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
        $tempImagePath = null;

        if ($request->hasFile('profile_picture')) {
            $tempImagePath = $request->file('profile_picture')->store('temp_pictures');
        }
        unset($data['profile_picture']);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = RoleEnum::STUDENT;
        $data['temp_image_path'] = $tempImagePath;

        Cache::put('registration_data_' . $data['email'], $data, now()->addMinutes(10));

        $otp = $this->otpService->generate($data['email'], 'numeric', 6, 10);
        Notification::route('mail', $data['email'])->notify(new SendRegistrationOtp($otp->token));

        return response()->json([
            'message' => 'تم إرسال رمز تحقق إلى بريدك الإلكتروني.',
            'email' => $data['email']
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $otpValidation = $this->otpService->validate($request->email, $request->otp_code);
        if (!$otpValidation->status) {
            return response()->json(['message' => $otpValidation->message], 400);
        }

        $registrationData = Cache::get('registration_data_' . $request->email);
        if (!$registrationData) {
            return response()->json(['message' => 'انتهت صلاحية جلسة التسجيل. يرجى المحاولة مرة أخرى.'], 400);
        }

        $tempImagePath = $registrationData['temp_image_path'] ?? null;
        unset($registrationData['temp_image_path']);

        $user = User::create($registrationData);
        $user->email_verified_at = Carbon::now();

        if ($tempImagePath && Storage::exists($tempImagePath)) {
            $extension = pathinfo(storage_path('app/' . $tempImagePath), PATHINFO_EXTENSION);
            $permanentPath = 'profile_pictures/user_' . $user->id . '_' . time() . '.' . $extension;
            Storage::move($tempImagePath, 'public/' . $permanentPath);
            $user->profile_picture = $permanentPath;
        }

        $user->save();
        Cache::forget('registration_data_' . $request->email);

        $token = $user->createToken('auth_token', ['*'], now()->addWeeks(2))->plainTextToken;

        return response()->json([
            'message' => 'تم التحقق من بريدك الإلكتروني وإنشاء الحساب بنجاح.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * إعادة إرسال رمز OTP (نسخة محدثة ومبسطة)
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $email = $request->email;

        // --- التعديل الرئيسي هنا ---
        // بما أن الـ OTP فقط للتسجيل الجديد، نتحقق فقط من الكاش
        if (Cache::has('registration_data_' . $email)) {
            $otp = $this->otpService->generate($email, 'numeric', 6, 10);
            Notification::route('mail', $email)->notify(new SendRegistrationOtp($otp->token));

            return response()->json([
                'message' => 'تم إعادة إرسال رمز التحقق إلى بريدك الإلكتروني.',
                'email' => $email
            ]);
        }

        return response()->json(['message' => 'لا يمكن إعادة إرسال الرمز لهذا البريد الإلكتروني.'], 400);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'بيانات الاعتماد غير صحيحة.'], 401);
        }

        $token = $user->createToken('auth_token', ['*'], now()->addWeeks(2))->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
