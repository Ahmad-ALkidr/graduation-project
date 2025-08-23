<?php
// app/Notifications/LibraryFileApprovedNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use App\Models\BookRequest;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class LibraryFileApprovedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $bookRequest;

    public function __construct(BookRequest $bookRequest)
    {
        $this->bookRequest = $bookRequest;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'file_id' => $this->bookRequest->id,
            'file_title' => $this->bookRequest->title,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => [
                'message' => "تمت الموافقة على ملفك '{$this->bookRequest->title}' وهو الآن متاح في المكتبة.",
                'file_id' => $this->bookRequest->id,
            ]
        ];
    }
    public function toFcm(object $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(FcmNotification::create()
                ->setTitle('تمت الموافقة على ملفك!')
                ->setBody("ملفك '{$this->bookRequest->title}' أصبح متاحًا الآن في المكتبة.")
            )
            ->setData(['file_id' => (string)$this->bookRequest->id]);
    }
}
