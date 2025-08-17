<?php

namespace App\Notifications;

use App\Models\PrivateMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewPrivateMessageNotification extends Notification
{
    use Queueable;

    protected PrivateMessage $message;

    public function __construct(PrivateMessage $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->first_name,
            'message_content' => $this->message->content,
            'type' => 'new_private_message'
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(FcmNotification::create()
                ->setTitle($this->message->sender->first_name)
                ->setBody($this->message->content)
            )
            ->setData([
                'conversation_id' => (string)$this->message->conversation_id,
                'sender_id' => (string)$this->message->sender_id,
                'type' => 'new_private_message'
            ]);
    }
}
