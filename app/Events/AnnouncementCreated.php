<?php

namespace App\Events;

use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Announcement $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function broadcastOn(): array
    {
        // سنقوم بالبث على قناة عامة اسمها 'announcements'
        return [new Channel('announcements')];
    }

    public function broadcastAs(): string
    {
        return 'announcement.created';
    }

    public function broadcastWith(): array
    {
        // إرسال الإعلان المنسق
        return ['announcement' => new AnnouncementResource($this->announcement)];
    }
}
