<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;      
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
// Protected Routes (Requires Authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    //Route::get('/user', function (Request $request) {
        //return $request->user();
    //});

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::patch('events/{event}/moderate', [EventController::class, 'moderate']);
        Route::patch('/registrations/{registration}/status', [OrganizerController::class, 'updateRegistrationStatus']);//  dont be confused by the name of the controller bc admin can also update the registration status for the attendee
        Route::get('/events/{event}/registrations', [OrganizerController::class, 'eventRegistrations']); // here the admin can see all events analitics , and again dont be confused about the name of the controller
        Route::get('/dashboard', [AdminController::class, 'dashboardOverview']);
        Route::get('/users', [AdminController::class, 'index']);
        Route::apiResources([
            'users' => AdminController::class,
            'events' => EventController::class,
            'categories' => CategoryController::class,
        ]);     
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    });

    Route::middleware('role:organizer')->prefix('organizer')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Welcome to the organizer dashboard']);
        });
        Route::get('/events/{event}/registrations', [OrganizerController::class, 'eventRegistrations']);
        Route::patch('/registrations/{registration}/status', [OrganizerController::class, 'updateRegistrationStatus']);
        Route::apiResources([
            'events' => EventController::class,
        ]);
    });

    Route::middleware('role:attendee')->prefix('attendee')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Welcome to the attendee dashboard']);
        });
        Route::post('/events/{event}/reviews', [ReviewController::class, 'store']);
        Route::get('/reviews', [ReviewController::class, 'getUserReviews']);
    });

    // Notification routes (accessible by all authenticated users)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    Route::post('/events/{event}/register', [PublicEventController::class, 'register']);

    // Payment Routes - Only for attendees
    Route::middleware('role:attendee')->group(function () {
        Route::prefix('payments')->group(function () {
            Route::post('/intent/{event}', [PaymentController::class, 'createPaymentIntent']);
            Route::get('/status/{paymentId}', [PaymentController::class, 'getPaymentStatus']);
            Route::get('/history', [PaymentController::class, 'getUserPayments']);
        });
    });
});
// Public routes
Route::get('/events/{event}/reviews', [ReviewController::class, 'getEventReviews']);
// Public Event Routes (No authentication required)
Route::prefix('public')->group(function () {
    Route::get('/events', [PublicEventController::class, 'index']);
    Route::get('/events/{event}', [PublicEventController::class, 'show']);
});

// Ticket verification route
Route::get('/tickets/verify/{registrationId}', [PublicEventController::class, 'verifyTicket'])
    ->name('tickets.verify');

// Stripe Webhook (No authentication required)
Route::post('/webhook/stripe', [PaymentController::class, 'handleWebhook']);

require __DIR__.'/auth.php';

