<?php

namespace App\Jobs;

use App\Models\PrivateMessage;
use App\Models\User;
use App\Notifications\NewPrivateMessageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PrivateMessage $message;
    protected int $recipientId;
    public $timeout = 30; // 30 ثانية timeout
    public $tries = 3; // إعادة المحاولة 3 مرات

    public function __construct(PrivateMessage $message, int $recipientId)
    {
        $this->message = $message;
        $this->recipientId = $recipientId;
    }

    public function handle(): void
    {
        try {
            $recipient = User::find($this->recipientId);

            if ($recipient) {
                // إرسال إشعار FCM
                $recipient->notify(new NewPrivateMessageNotification($this->message));

                // يمكن إضافة المزيد من المهام هنا مثل:
                // - إرسال إشعار بريد إلكتروني
                // - تحديث الإحصائيات
                // - معالجة الملفات

                Log::info("Notification sent successfully to user {$this->recipientId}");
            } else {
                Log::warning("Recipient user {$this->recipientId} not found");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification: " . $e->getMessage());
            throw $e; // إعادة رمي الخطأ لإعادة المحاولة
        }
    }

    /**
     * معالجة فشل Job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Message notification job failed permanently", [
            'recipient_id' => $this->recipientId,
            'message_id' => $this->message->id,
            'error' => $exception->getMessage()
        ]);
    }
}
