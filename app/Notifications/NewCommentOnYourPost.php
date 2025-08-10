<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;


class NewCommentOnYourPost extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // سنرسل الإشعار إلى قاعدة البيانات (للعرض لاحقاً)
        // وإلى الويب سوكيت (للعرض اللحظي)
        return ['database', 'broadcast', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification.
     * (هذا لتخزين الإشعار في قاعدة البيانات)
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->comment->post->id,
            'post_title' => $this->comment->post->title, // افترض أن للمنشور عنوان
            'commenter_id' => $this->comment->user->id,
            'commenter_name' => $this->comment->user->name,
            'comment_excerpt' => \Illuminate\Support\Str::limit($this->comment->content, 50),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     * (هذا للإرسال عبر الويب سوكيت)
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => [
                'message' => "قام {$this->comment->user->name} بالتعليق على منشورك.",
                'post_id' => $this->comment->post->id,
                'commenter_name' => $this->comment->user->name,
            ]
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(FcmNotification::create()
                ->setTitle('تعليق جديد!')
                ->setBody("قام {$this->comment->user->first_name} بالتعليق على منشورك.")
            )
            ->setData(['post_id' => (string)$this->comment->post->id]);
    }
}
