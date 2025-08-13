<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordResetOtp extends Notification
{
    use Queueable;
    protected string $otpCode;

    public function __construct(string $otpCode)
    {
        $this->otpCode = $otpCode;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'شام يونيتي');
        return (new MailMessage)
            ->subject("إعادة تعيين كلمة المرور لـ {$appName}")
            ->greeting('مرحبًا يا ' . $notifiable->first_name . '!')
            ->line('رمز التحقق الخاص بك لإعادة تعيين كلمة المرور هو:')
            ->line('**' . $this->otpCode . '**')
            ->line('هذا الرمز صالح لمدة 15 دقيقة.');
    }
}
