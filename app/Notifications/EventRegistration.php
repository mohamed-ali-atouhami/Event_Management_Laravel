<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    protected $registration;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "New registration for event: {$this->registration->event->title}",
            'registration_id' => $this->registration->id,
            'event_id' => $this->registration->event_id,
            'user_id' => $this->registration->user_id,
            'type' => 'new_registration'
        ];
    }
}