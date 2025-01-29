<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentSuccessful extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Payment successful for event: {$this->payment->event->title}",
            'payment_id' => $this->payment->id,
            'event_id' => $this->payment->event_id,
            'amount' => $this->payment->amount,
            'type' => 'payment_successful'
        ];
    }
}
