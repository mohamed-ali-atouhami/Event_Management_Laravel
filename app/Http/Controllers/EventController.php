<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Start with a base query
        $query = Event::query()->with('organizer');

        // If user is an attendee, show only approved events
        if (auth()->user()->role === 'attendee') {
            $query->where('status', 'approved');
        }
        // If user is an organizer, show only their events
        elseif (auth()->user()->role === 'organizer') {
            $query->where('organizer_id', auth()->id());
        }
        // Admin can see all events

        // Apply location filter
        if ($request->has('location')) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }

        // // Apply title filter
        if ($request->has('title')) {
            $query->where('title', 'LIKE', '%' . $request->title . '%');
        }

        $events = $query->get();
        return EventResource::collection($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request) : JsonResponse
    {
        // Check if user has permission to create events
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'organizer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validateForms = $request->validated();
        
        // Set the organizer_id to the current user
        $validateForms['organizer_id'] = auth()->id();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store in public/images/ directory
            $path = Storage::disk('public')->putFileAs('images', $file, $filename);
            $validateForms['image'] = '/storage/' . $path;
        }
        // Set initial status as pending
        $validated['status'] = 'pending';
        $event = Event::create($validateForms);
        $response = new EventResource($event);
        return response()->json([
            "event" => $response,
            "message" => __("Event Created successfully !!")
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event) : JsonResponse
    {
        // Check if user has permission to view this event
        if (auth()->user()->role === 'attendee' && $event->status !== 'approved') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if (auth()->user()->role === 'organizer' && $event->organizer_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $response =  new EventResource($event->load('organizer'));
        return response()->json([
            "event" => $response,
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event) : JsonResponse
    {
        // Check if user has permission to update this event
        if (!auth()->user()->isAdmin() && 
            (auth()->user()->role !== 'organizer' || $event->organizer_id !== auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validateForms = $request->validated();
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($event->image) {
                $oldPath = str_replace('/storage/', '', $event->image);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store in public/images/events directory
            $path = Storage::disk('public')->putFileAs('images', $file, $filename);
            $validateForms['image'] = '/storage/' . $path;
        }

        $event->update($validateForms);
        $response = new EventResource($event);
        return response()->json([
            "event" => $response,
            "message" => __("Event Updated successfully !!")
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event) : JsonResponse
    {
        // Check if user has permission to delete this event
        if (!auth()->user()->isAdmin() && 
            (auth()->user()->role !== 'organizer' || $event->organizer_id !== auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        //
        if ($event->image) {
            $oldPath = str_replace('/storage/', '', $event->image);
            Storage::disk('public')->delete($oldPath);
        }   
        $event->delete();
        $response =  new EventResource($event);
        return response()->json([
            "event" => $response,
            "message" => __("Event Deleted successfully !!")
        ],200);
    }

    /**
     * Moderate an event (admin only)
     */
    public function moderate(Event $event, Request $request) : JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|nullable'
        ]);

        $event->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null
        ]);

        return response()->json([
            'event' => new EventResource($event),
            'message' => __('Event status updated successfully!')
        ]);
    }
}
