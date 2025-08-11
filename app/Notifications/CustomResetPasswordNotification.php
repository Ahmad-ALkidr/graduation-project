<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        // --- هذا هو التعديل الرئيسي ---
        // نقوم ببناء الرابط العميق مباشرة هنا
        $deepLinkUrl = 'shamunity://reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
                    ->subject('إشعار إعادة تعيين كلمة المرور')
                    ->line('أنت تستقبل هذا البريد الإلكتروني لأننا تلقينا طلبًا لإعادة تعيين كلمة المرور لحسابك.')
                    ->action('إعادة تعيين كلمة المرور', $deepLinkUrl) // <-- استخدام الرابط العميق مباشرة
                    ->line('هذا الرابط صالح لمدة 60 دقيقة.')
                    ->line('إذا لم تطلب إعادة تعيين كلمة المرور، فلا داعي لاتخاذ أي إجراء آخر.');
    }
}
