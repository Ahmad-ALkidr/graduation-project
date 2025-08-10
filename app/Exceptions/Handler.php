<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // --- هذا هو التعديل الرئيسي ---
        // هذا هو المكان الوحيد الذي نحتاج لتعريف معالجة الخطأ فيه
        $this->renderable(function (AuthenticationException $e, $request) {
            // تأكد من أن الطلب هو طلب API
            if ($request->expectsJson()) {
                // أعد رسالة الخطأ المخصصة مع الرمز القياسي 422
                return response()->json(
                    [
                        'message' => 'الجلسة منتهية أو التوكن غير صالح. يرجى تسجيل الدخول مجدداً.'
                    ],
                    422 // <-- تم تصحيح رمز الحالة إلى 401 Unauthorized
                );
            }
        });
    }
}
