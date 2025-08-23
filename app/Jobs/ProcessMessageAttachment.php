<?php

namespace App\Jobs;

use App\Models\PrivateMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessMessageAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PrivateMessage $message;
    public $timeout = 60; // 60 ثانية للملفات
    public $tries = 2;

    public function __construct(PrivateMessage $message)
    {
        $this->message = $message;
    }

    public function handle(): void
    {
        try {
            if ($this->message->type === 'text') {
                return; // لا حاجة لمعالجة النصوص
            }

            // معالجة الملفات حسب النوع
            switch ($this->message->type) {
                case 'image':
                    $this->processImage();
                    break;
                case 'video':
                    $this->processVideo();
                    break;
                case 'audio':
                    $this->processAudio();
                    break;
                case 'file':
                    $this->processFile();
                    break;
            }

            Log::info("Attachment processed successfully for message {$this->message->id}");
        } catch (\Exception $e) {
            Log::error("Failed to process attachment: " . $e->getMessage());
            throw $e;
        }
    }

    private function processImage(): void
    {
        // يمكن إضافة معالجة الصور مثل:
        // - إنشاء thumbnails
        // - ضغط الصور
        // - تحويل الصيغ
    }

    private function processVideo(): void
    {
        // يمكن إضافة معالجة الفيديو مثل:
        // - إنشاء thumbnails
        // - ضغط الفيديو
        // - استخراج metadata
    }

    private function processAudio(): void
    {
        // يمكن إضافة معالجة الصوت مثل:
        // - ضغط الملفات
        // - استخراج metadata
    }

    private function processFile(): void
    {
        // يمكن إضافة معالجة الملفات مثل:
        // - فحص الفيروسات
        // - إنشاء preview
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Attachment processing job failed permanently", [
            'message_id' => $this->message->id,
            'error' => $exception->getMessage()
        ]);
    }
}
