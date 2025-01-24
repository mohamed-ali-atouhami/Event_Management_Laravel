<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Registration;

class OrganizerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display registrations for a specific event
     */
    public function eventRegistrations(string $eventId)
    {
        $event = Event::with(['registrations.user'])->findOrFail($eventId);
        
        $registrations = $event->registrations()->with('user')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->select('registrations.*')
            ->selectRaw('CASE 
                WHEN registrations.status = "confirmed" THEN events.ticket_price 
                ELSE 0 
            END as revenue')
            ->get();

        return response()->json([
            'event' => $event,
            'registrations' => $registrations,
            'analytics' => [
                'total_registrations' => $registrations->count(),
                'confirmed_registrations' => $registrations->where('status', 'confirmed')->count(),
                'cancelled_registrations' => $registrations->where('status', 'cancelled')->count(),
                'total_revenue' => $registrations->sum('revenue')
            ]
        ]);
    }

    /**
     * Update registration status
     */
    public function updateRegistrationStatus(Request $request, string $registrationId)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled'
        ]);

        $registration = Registration::findOrFail($registrationId);
        
        // Check if the authenticated organizer owns the event
        if ($registration->event->organizer_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $registration->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Registration status updated successfully',
            'registration' => $registration
        ]);
    }
}
