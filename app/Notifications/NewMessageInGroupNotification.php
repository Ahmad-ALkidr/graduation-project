<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewMessageInGroupNotification extends Notification
{
    use Queueable;

    protected Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        // سنرسل الإشعار لقاعدة البيانات وخدمة FCM فقط
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'group_id' => $this->message->chat_group_id,
            'group_name' => $this->message->group->name,
            'sender_id' => $this->message->user->id,
            'sender_name' => $this->message->user->first_name,
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(FcmNotification::create()
                ->setTitle($this->message->group->name)
                ->setBody($this->message->user->first_name . ': ' . $this->message->content)
            )
            ->setData([
                'group_id' => (string)$this->message->chat_group_id,
                'type' => 'new_chat_message' // نوع مخصص لمساعدة التطبيق
            ]);
    }
}
