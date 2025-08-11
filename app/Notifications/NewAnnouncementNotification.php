<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewAnnouncementNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Announcement $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'content_preview' => \Str::limit($this->announcement->content, 50),
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => [
                'message' => 'إعلان جديد من الجامعة!',
                'announcement_id' => $this->announcement->id,
            ]
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(FcmNotification::create()
                ->setTitle('إعلان جديد من الجامعة')
                ->setBody($this->announcement->content)
            )
            ->setData(['announcement_id' => (string)$this->announcement->id]);
    }
}
