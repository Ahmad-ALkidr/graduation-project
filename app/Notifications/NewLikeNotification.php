<?php
// app/Notifications/NewLikeNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Post;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewLikeNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $liker;
    protected $post;

    public function __construct(User $liker, Post $post)
    {
        $this->liker = $liker;
        $this->post = $post;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class]; // إرسال لقاعدة البيانات والبث اللحظي
    }

    // لتخزين الإشعار في قاعدة البيانات
    public function toArray(object $notifiable): array
    {
        return [
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->first_name,
            'post_id' => $this->post->id,
        ];
    }

    // للإرسال عبر الويب سوكيت
    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => [
                'message' => "قام {$this->liker->first_name} بالإعجاب بمنشورك.",
                'post_id' => $this->post->id,
            ]
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(FcmNotification::create()
                ->setTitle('إعجاب جديد!')
                ->setBody("قام {$this->liker->first_name} بالإعجاب بمنشورك.")
            )
            ->setData(['post_id' => (string)$this->post->id]);
    }
}
