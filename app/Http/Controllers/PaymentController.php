<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Notification;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;


class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create payment intent for Stripe
     */
    public function createPaymentIntent(Event $event)
    {
        // Add role verification
        if (auth()->user()->role !== 'attendee') {
            return response()->json([
                'message' => 'Only attendees can make payments'
            ], 403);
        }

        try {
            // Check if user already has a pending payment for this event
            $existingPayment = Payment::where([
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'status' => 'pending'
            ])->first();

            if ($existingPayment) {
                return response()->json([
                    'message' => 'You already have a pending payment for this event'
                ], 400);
            }

            // Create a PaymentIntent with the order amount and currency
            $paymentIntent = PaymentIntent::create([
                'amount' => $event->ticket_price * 100, // Amount in cents
                'currency' => 'usd',
                'metadata' => [
                    'event_id' => $event->id,
                    'user_id' => auth()->id()
                ]
            ]);

            // Create a pending payment record
            $payment = Payment::create([
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'amount' => $event->ticket_price,
                'status' => 'pending'
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'payment_id' => $payment->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating payment intent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle successful payment webhook from Stripe
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        if (!$endpoint_secret) {
            Log::error('Stripe webhook secret is not configured');
            return response()->json(['message' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $sig_header,
                $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['message' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            
            // Update payment status
            $payment = Payment::where([
                'user_id' => $paymentIntent->metadata->user_id,
                'event_id' => $paymentIntent->metadata->event_id,
                'status' => 'pending'
            ])->first();

            if ($payment) {
                $payment->update(['status' => 'completed']);

                // Create or update registration
                $registration = Registration::updateOrCreate(
                    [
                        'user_id' => $paymentIntent->metadata->user_id,
                        'event_id' => $paymentIntent->metadata->event_id,
                    ],
                    ['status' => 'confirmed']
                );

                // Create notification
                Notification::create([
                    'user_id' => $paymentIntent->metadata->user_id,
                    'message' => 'Payment successful for event: ' . $registration->event->title,
                    'is_read' => false
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($paymentId)
    {
        $payment = Payment::with(['event', 'user'])->findOrFail($paymentId);
        
        return response()->json([
            'status' => $payment->status,
            'amount' => $payment->amount,
            'event' => $payment->event->title,
            'created_at' => $payment->created_at
        ]);
    }

    /**
     * Get user payments history
     */
    public function getUserPayments()
    {
        $payments = Payment::with('event')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payments);
    }
} 