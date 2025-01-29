<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;
    protected $status;

    public function __construct(Event $event, $status)
    {
        $this->event = $event;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Event '{$this->event->title}' has been {$this->status}",
            'event_id' => $this->event->id,
            'status' => $this->status,
            'type' => 'event_status_changed'
        ];
    }
}
