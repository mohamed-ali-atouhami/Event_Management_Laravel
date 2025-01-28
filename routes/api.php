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

// Protected Routes (Requires Authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    //Route::get('/user', function (Request $request) {
        //return $request->user();
    //});

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::patch('events/{event}/moderate', [EventController::class, 'moderate']);
        Route::get('/dashboard', [AdminController::class, 'dashboardOverview']);
        Route::get('/users', [AdminController::class, 'index']);
        Route::apiResources([
            'users' => AdminController::class,
            'events' => EventController::class,
            'categories' => CategoryController::class,
        ]);     
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

