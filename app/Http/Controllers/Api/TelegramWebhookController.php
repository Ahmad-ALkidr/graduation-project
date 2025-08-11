<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\NewAnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram update received:', $update);

        if (isset($update['channel_post'])) {
            $post = $update['channel_post'];
            $messageId = $post['message_id'];

            if (Announcement::where('telegram_message_id', $messageId)->exists()) {
                return response()->json(['status' => 'ok', 'message' => 'Already processed']);
            }

            $content = $post['caption'] ?? $post['text'] ?? null;
            $filePath = null;
            $fileType = null;
            $fileData = null;

            // --- هذا هو التعديل الرئيسي ---
            // التحقق من أنواع الملفات المختلفة
            if (isset($post['photo'])) {
                $fileType = 'image';
                $fileData = end($post['photo']); // احصل على أكبر صورة
            } elseif (isset($post['document'])) {
                $fileType = 'document';
                $fileData = $post['document'];
            } elseif (isset($post['video'])) {
                $fileType = 'video';
                $fileData = $post['video'];
            }

            // إذا وجدنا أي نوع من الملفات، قم بتنزيله
            if ($fileData) {
                $fileId = $fileData['file_id'];
                // احصل على الاسم الأصلي للملف إن وجد
                $originalFileName = $fileData['file_name'] ?? uniqid() . '.tmp';

                try {
                    $file = Telegram::getFile(['file_id' => $fileId]);
                    $fileContents = file_get_contents('https://api.telegram.org/file/bot' . config('telegram.bots.mybot.token') . '/' . $file->getFilePath());

                    $filePathToStore = 'announcements/' . $originalFileName;
                    Storage::put('public/' . $filePathToStore, $fileContents);
                    $filePath = $filePathToStore;
                } catch (\Exception $e) {
                    Log::error('Failed to download Telegram file: ' . $e->getMessage());
                }
            }

            $announcement = Announcement::create([
                'content' => $content,
                'file_path' => $filePath,
                'file_type' => $fileType,
                'telegram_message_id' => $messageId,
            ]);

            $users = User::all();
            Notification::send($users, new NewAnnouncementNotification($announcement));
        }

        return response()->json(['status' => 'ok']);
    }
}
