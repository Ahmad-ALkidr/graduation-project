<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendRegistrationOtp extends Notification
{
    use Queueable;

    protected string $otpCode;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otpCode)
    {
        $this->otpCode = $otpCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'شام يونيتي');

        return (new MailMessage)
                    ->subject("رمز التحقق الخاص بك لـ {$appName}")
                    ->greeting("مرحبًا بك في {$appName}!")
                    ->line('رمز التحقق الخاص بك لإكمال عملية التسجيل هو:')
                    ->line('**' . $this->otpCode . '**')
                    ->line('هذا الرمز صالح لمدة 10 دقائق.')
                    ->line("شكرًا لانضمامك إلينا!");
    }
}
