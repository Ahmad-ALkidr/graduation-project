<?php
// app/Notifications/NewPostInDepartmentNotification.php (تحديث)

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Post;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewPostInDepartmentNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $poster;
    protected $post;

    public function __construct(User $poster, Post $post)
    {
        $this->poster = $poster;
        $this->post = $post;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'poster_id' => $this->poster->id,
            'poster_name' => $this->poster->first_name,
            'post_id' => $this->post->id,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => [
                'message' => "قام زميلك {$this->poster->first_name} بنشر منشور جديد في قسمك.",
                'post_id' => $this->post->id,
            ]
        ];
    }
       /**
     * تحديد شكل إشعار الدفع
     */

    /**
     * تحديد شكل إشعار الدفع
     */
    public function toFcm(object $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(FcmNotification::create()
                ->setTitle('منشور جديد في قسمك')
                ->setBody("قام زميلك {$this->poster->first_name} بنشر منشور جديد.")
            )
            ->setData(['post_id' => (string)$this->post->id]);
    }
}
