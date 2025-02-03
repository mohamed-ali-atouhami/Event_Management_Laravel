<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Review;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request, Event $event)
    {
        // Validate request
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500'
        ]);

        // Check if user attended the event
        $registration = Registration::where([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'status' => 'confirmed'
        ])->first();

        if (!$registration) {
            return response()->json([
                'message' => 'You must attend the event before leaving a review'
            ], 403);
        }

        // Check if event has passed
        if (now() <= $event->date) {
            return response()->json([
                'message' => 'You can only review events that have already taken place'
            ], 403);
        }

        // Check if user already reviewed this event
        $existingReview = Review::where([
            'event_id' => $event->id,
            'user_id' => auth()->id()
        ])->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this event'
            ], 400);
        }

        // Create review
        $review = Review::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment']
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review
        ], 201);
    }

    /**
     * Get reviews for an event
     */
    public function getEventReviews(Event $event)
    {
        $reviews = Review::with('user:id,name')
            ->where('event_id', $event->id)
            ->latest()
            ->paginate(10);

        $averageRating = Review::where('event_id', $event->id)->avg('rating');

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => Review::where('event_id', $event->id)->count()
        ]);
    }

    /**
     * Delete a review (admin only)
     */
    public function destroy(Review $review)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews()
    {
        $reviews = Review::with('event:id,title')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }
} 