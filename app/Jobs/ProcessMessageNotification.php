<?php

namespace App\Jobs;

use App\Models\PrivateMessage;
use App\Models\User;
use App\Notifications\NewPrivateMessageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PrivateMessage $message;
    protected int $recipientId;

    public function __construct(PrivateMessage $message, int $recipientId)
    {
        $this->message = $message;
        $this->recipientId = $recipientId;
    }

    public function handle(): void
    {
        $recipient = User::find($this->recipientId);
        
        if ($recipient) {
            $recipient->notify(new NewPrivateMessageNotification($this->message));
        }
    }
}
