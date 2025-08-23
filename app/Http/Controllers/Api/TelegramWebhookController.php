<?php

namespace App\Http\Controllers\Api;

// ... (Use statements remain the same)
use App\Http\Controllers\Controller;
use App\Events\AnnouncementCreated;
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

        // تحقق من وجود منشور قناة أو رسالة عادية
        $post = $update['channel_post'] ?? $update['message'] ?? null;

        if ($post) {
            $messageId = $post['message_id'];

            if (Announcement::where('telegram_message_id', $messageId)->exists()) {
                return response()->json(['status' => 'ok', 'message' => 'Already processed']);
            }

            // --- باقي الكود يبقى كما هو ---
            $content = $post['caption'] ?? $post['text'] ?? null;
            $filePath = null;
            $fileType = null;
            $fileData = null;

            if (isset($post['photo'])) {
                $fileType = 'image';
                $fileData = end($post['photo']);
            } elseif (isset($post['document'])) {
                $fileType = 'document';
                $fileData = $post['document'];
            } elseif (isset($post['video'])) {
                $fileType = 'video';
                $fileData = $post['video'];
            }

            if ($fileData) {
                $fileId = $fileData['file_id'];
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

            // --- تحقق من وجود محتوى نصي أو ملف قبل الإنشاء ---
            if ($content || $filePath) {
                 $announcement = Announcement::create([
                    'content' => $content,
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                    'telegram_message_id' => $messageId,
                ]);
                broadcast(new AnnouncementCreated($announcement));

                $users = User::all();
                Notification::send($users, new NewAnnouncementNotification($announcement));
            } else {
                 Log::warning('No content or file found in the message.', ['message_id' => $messageId]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
