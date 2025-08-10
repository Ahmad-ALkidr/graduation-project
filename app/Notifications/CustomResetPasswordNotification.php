<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString; // <-- إضافة مهمة

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // 1. بناء رابط إعادة التوجيه
        $redirectUrl = url('/password/reset/redirect?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]));

        // --- 2. هذا هو التعديل الرئيسي ---
        // سنقوم ببناء الزر يدويًا باستخدام HTML
        $buttonStyle = "display: inline-block; padding: 12px 24px; font-size: 16px; color: #ffffff; background-color: #2d3748; border-radius: 6px; text-decoration: none;";
        $buttonHtml = '<a href="' . $redirectUrl . '" style="' . $buttonStyle . '">إعادة تعيين كلمة المرور</a>';

        // 3. بناء الرسالة بدون استخدام ->action()
        return (new MailMessage)
                    ->subject('إشعار إعادة تعيين كلمة المرور')
                    ->greeting('ShamUnity مرحبًا بك في تطبيق ')
                    ->line('أنت تستقبل هذا البريد الإلكتروني لأننا تلقينا طلبًا لإعادة تعيين كلمة المرور لحسابك.')
                    ->line(new HtmlString('<div style="text-align: center; margin: 20px 0;">' . $buttonHtml . '</div>')) // وضع الزر في المنتصف
                    ->line('هذا الرابط صالح لمدة 60 دقيقة.')
                    ->line('إذا لم تطلب إعادة تعيين كلمة المرور، فلا داعي لاتخاذ أي إجراء آخر.');
    }
}
