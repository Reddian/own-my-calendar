<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\CalendarGradeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\MultiCalendarController;
use App\Http\Controllers\AIGradingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StripeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // User profile routes
    Route::get('/profile', [UserProfileController::class, 'index']);
    Route::post('/profile', [UserProfileController::class, 'store']);
    Route::get('/profile/me', [UserProfileController::class, 'show']);
    Route::put('/profile/me', [UserProfileController::class, 'update']);

    // Onboarding routes
    Route::get('/onboarding', [OnboardingController::class, 'index']);
    Route::post('/onboarding', [OnboardingController::class, 'store']);

    // Calendar grade routes
    Route::get('/grades', [CalendarGradeController::class, 'index']);
    Route::post('/grades', [CalendarGradeController::class, 'store']);
    Route::get('/grades/current-week', [CalendarGradeController::class, 'getCurrentWeekGrade']);
    Route::get('/grades/{id}', [CalendarGradeController::class, 'show']);
    Route::post('/grades/date-range', [CalendarGradeController::class, 'getGradesByDateRange']);

    // Google Calendar routes (Legacy - Single Calendar)
//    Route::get('/google/redirect', [GoogleCalendarController::class, 'redirectToGoogle']);
//    Route::get('/google/callback', [GoogleCalendarController::class, 'handleGoogleCallback']);
    Route::get('/google/check-connection', [GoogleCalendarController::class, 'checkConnection']);
    Route::post('/google/events', [GoogleCalendarController::class, 'getEvents']);
    Route::post('/google/disconnect', [GoogleCalendarController::class, 'disconnect']);

    // Multiple Google Calendar routes
    Route::get('/calendars/auth', [MultiCalendarController::class, 'getAuthUrl']);
    Route::get('/calendars/callback', [MultiCalendarController::class, 'handleCallback']);
    Route::get('/calendars', [MultiCalendarController::class, 'getUserCalendars']);
    Route::post('/calendars/selection', [MultiCalendarController::class, 'updateCalendarSelection']);
    Route::post('/calendars/visibility', [MultiCalendarController::class, 'updateCalendarVisibility']);
    Route::post('/calendars/events', [MultiCalendarController::class, 'getEvents']);
    Route::post('/calendars/disconnect', [MultiCalendarController::class, 'disconnectCalendar']);
    Route::post('/calendars/disconnect-all', [MultiCalendarController::class, 'disconnectAllCalendars']);
    Route::get('/calendars/check-connection', [MultiCalendarController::class, 'checkConnection']);

    // AI Grading routes
    Route::post('/ai/grade-calendar', [AIGradingController::class, 'gradeCalendar']);

    // Subscription routes
    Route::get('/subscription', [SubscriptionController::class, 'index']);
    Route::post('/subscription/checkout', [SubscriptionController::class, 'createCheckoutSession']);
    Route::get('/subscription/success', [SubscriptionController::class, 'success']);
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription']);
    Route::get('/subscription/can-grade', [SubscriptionController::class, 'canGradeCalendar']);
    Route::post('/subscription/increment-grades', [SubscriptionController::class, 'incrementGradesUsed']);

    // Google Calendar Routes
    Route::prefix('calendars')->group(function () {
        Route::get('/', [GoogleCalendarController::class, 'getCalendars']);
        Route::get('/auth', [GoogleCalendarController::class, 'getAuthUrl']);
        Route::get('/check-connection', [GoogleCalendarController::class, 'checkConnection']);
        Route::post('/selection', [GoogleCalendarController::class, 'updateSelection']);
        Route::post('/visibility', [GoogleCalendarController::class, 'updateVisibility']);
        Route::post('/disconnect', [GoogleCalendarController::class, 'disconnectCalendar']);
        Route::post('/disconnect-all', [GoogleCalendarController::class, 'disconnectAll']);
        Route::post('/disconnect-google', [GoogleCalendarController::class, 'disconnectGoogle']);
    });

    // Stripe Routes
    Route::post('/stripe/create-checkout-session', [StripeController::class, 'createCheckoutSession']);
    Route::get('/stripe/checkout/success', [StripeController::class, 'handleCheckoutSuccess']);
    Route::post('/stripe/subscription/cancel', [StripeController::class, 'cancelSubscription']);
    Route::post('/stripe/payment-method', [StripeController::class, 'updatePaymentMethod']);
    Route::get('/stripe/payment-methods', [StripeController::class, 'getPaymentMethods']);
    Route::get('/stripe/subscription/status', [StripeController::class, 'getSubscriptionStatus']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
