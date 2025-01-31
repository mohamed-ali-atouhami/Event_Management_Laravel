<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use App\Notifications\EventRegistration;
use App\Models\User;

class PublicEventController extends Controller
{
    /**
     * List all approved events with filters
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Event::query()
            ->where('status', 'approved')
            ->with(['organizer', 'categories']);

        // Apply date filter
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // Apply location filter
        if ($request->has('location')) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }

        // Apply category filter
        // if ($request->has('category_id')) {
        //     $query->whereHas('categories', function($q) use ($request) {
        //         $q->where('categories.id', $request->category_id);
        //     });
        // }

        // Apply price range filter
        if ($request->has('min_price')) {
            $query->where('ticket_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('ticket_price', '<=', $request->max_price);
        }

        $events = $query->paginate(10);
        return EventResource::collection($events);
    }

    /**
     * Show event details
     */
    public function show(Event $event)
    {
        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json($event->load(['organizer', 'categories']));
    }

    /**
     * Register for an event
     */
    public function register(Request $request, Event $event)
    {
        // Check if event is approved
        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Event is not available for registration'], 403);
        }

        // Check if user is already registered
        $existingRegistration = Registration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingRegistration) {
            return response()->json(['message' => 'You are already registered for this event'], 400);
        }

        // Create registration
        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        // Generate QR code
        $qrCode = QrCode::size(300)->generate(route('tickets.verify', $registration->id));

        // Create ticket with QR code
        $ticket = Ticket::create([
            'registration_id' => $registration->id,
            'qr_code' => $qrCode
        ]);

        // Notify organizer about new registration
        $event->organizer->notify(new EventRegistration($registration));

        // Notify admins about new registration
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new EventRegistration($registration));
        }

        return response()->json([
            'message' => 'Registration successful',
            'registration' => $registration,
            'ticket' => $ticket
        ], 201);
    }

    /**
     * Verify ticket
     */
    public function verifyTicket(string $registrationId)
    {
        try {
            $registration = Registration::with(['event', 'user', 'tickets'])
                ->findOrFail($registrationId);

            // Check if registration status is confirmed
            if ($registration->status !== 'confirmed') {
                return response()->json([
                    'valid' => false,
                    'message' => 'Registration is not confirmed. Current status: ' . $registration->status
                ], 400);
            }

            // Check if ticket exists
            if ($registration->tickets->isEmpty()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'No ticket found for this registration'
                ], 404);
            }

            // Check if event date has passed
            if (now() > $registration->event->date) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Event has already passed'
                ], 400);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Ticket is valid',
                'registration' => $registration
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Registration not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'An error occurred while verifying the ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 