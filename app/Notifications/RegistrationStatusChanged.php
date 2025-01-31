<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegistrationStatusChanged extends Notification implements ShouldQueue
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
            'message' => "Your registration status for event '{$this->registration->event->title}' has been changed to {$this->registration->status}",
            'registration_id' => $this->registration->id,
            'event_id' => $this->registration->event_id,
            'status' => $this->registration->status,
            'type' => 'registration_status_changed'
        ];
    }
} 