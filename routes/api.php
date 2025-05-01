<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken; // Import VerifyCsrfToken
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController; // Import RegisterController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import ForgotPasswordController
use App\Http\Controllers\Auth\ResetPasswordController; // Import ResetPasswordController
use App\Http\Controllers\Auth\VerificationController; // Import VerificationController
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\CalendarGradeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\MultiCalendarController;
use App\Http\Controllers\AIGradingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\ExtensionController;

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

// Public Authentication API routes
Route::post("/login", [LoginController::class, "apiLogin"]);
Route::post("/register", [RegisterController::class, "apiRegister"]); // Add API register route
Route::post("/password/email", [ForgotPasswordController::class, "sendResetLinkEmailApi"]); // Add API forgot password route
Route::post("/password/reset", [ResetPasswordController::class, "resetApi"]); // Add API reset password route

// Routes requiring authentication (Sanctum)
Route::middleware("auth:sanctum")->group(function () {
    // Logout
    Route::post("/logout", [LoginController::class, "apiLogout"]);

    // Email Verification
    // Note: Verification routes might need specific middleware from Laravel Breeze/UI if used
    Route::post("/email/resend", [VerificationController::class, "resendApi"])->name("verification.resend.api"); // Add API resend verification route
    // Laravel's default verification routes often include signed URLs, which might need adjustments for API usage.
    // Route::get("/email/verify/{id}/{hash}", [VerificationController::class, "verify"])->name("verification.verify"); // Example if needed

    // User profile routes
    Route::get("/profile", [UserProfileController::class, "index"]);
    Route::post("/profile", [UserProfileController::class, "store"]);
    Route::get("/profile/me", [UserProfileController::class, "show"]);
    Route::put("/profile/me", [UserProfileController::class, "update"]);

    // Onboarding routes
    Route::get("/onboarding", [OnboardingController::class, "index"]);
    Route::post("/onboarding", [OnboardingController::class, "store"]);

    // Calendar grade routes
    Route::get("/grades", [CalendarGradeController::class, "index"]);
    Route::post("/grades", [CalendarGradeController::class, "store"]);
    Route::get("/grades/current-week", [CalendarGradeController::class, "getCurrentWeekGrade"]);
    Route::get("/grades/{id}", [CalendarGradeController::class, "show"]);
    Route::post("/grades/date-range", [CalendarGradeController::class, "getGradesByDateRange"]);

    // Google Calendar routes (Legacy - Single Calendar)
//    Route::get("/google/redirect", [GoogleCalendarController::class, "redirectToGoogle"]);
//    Route::get("/google/callback", [GoogleCalendarController::class, "handleGoogleCallback"]);
    Route::get("/google/check-connection", [GoogleCalendarController::class, "checkConnection"]);
    Route::post("/google/events", [GoogleCalendarController::class, "getEvents"]);
    Route::post("/google/disconnect", [GoogleCalendarController::class, "disconnect"]);

    // Multiple Google Calendar routes
    Route::get("/calendars/auth", [MultiCalendarController::class, "getAuthUrl"]);
    Route::get("/calendars/callback", [MultiCalendarController::class, "handleCallback"]);
    Route::get("/calendars", [MultiCalendarController::class, "getUserCalendars"]);
    Route::post("/calendars/selection", [MultiCalendarController::class, "updateCalendarSelection"]);
    Route::post("/calendars/visibility", [MultiCalendarController::class, "updateCalendarVisibility"]);
    Route::post("/calendars/events", [MultiCalendarController::class, "getEvents"]);
    Route::post("/calendars/disconnect", [MultiCalendarController::class, "disconnectCalendar"]);
    Route::post("/calendars/disconnect-all", [MultiCalendarController::class, "disconnectAllCalendars"]);
    Route::get("/calendars/check-connection", [MultiCalendarController::class, "checkConnection"]);

    // AI Grading routes
    Route::post("/ai/grade-calendar", [AIGradingController::class, "gradeCalendar"]);

    // Subscription routes
    Route::get("/subscription", [SubscriptionController::class, "index"]);
    Route::post("/subscription/checkout", [SubscriptionController::class, "createCheckoutSession"]);
    Route::get("/subscription/success", [SubscriptionController::class, "success"]);
    Route::get("/subscription/cancel", [SubscriptionController::class, "cancel"]);
    Route::post("/subscription/cancel", [SubscriptionController::class, "cancelSubscription"]);
    Route::get("/subscription/can-grade", [SubscriptionController::class, "canGradeCalendar"]);
    Route::post("/subscription/increment-grades", [SubscriptionController::class, "incrementGradesUsed"]);

    // Google Calendar Routes (Refactored)
    Route::prefix("calendars")->group(function () {
        Route::get("/", [GoogleCalendarController::class, "getCalendars"]);
        Route::get("/auth", [GoogleCalendarController::class, "getAuthUrl"]);
        Route::get("/check-connection", [GoogleCalendarController::class, "checkConnection"]);
        Route::post("/update-selection", [GoogleCalendarController::class, "updateSelection"]);
        Route::post("/visibility", [GoogleCalendarController::class, "updateVisibility"]);
        Route::post("/disconnect", [GoogleCalendarController::class, "disconnectCalendar"]);
        Route::post("/disconnect-all", [GoogleCalendarController::class, "disconnectAll"]);
        Route::post("/disconnect-google", [GoogleCalendarController::class, "disconnectGoogle"]);
    });

    // Stripe Routes
    Route::post("/stripe/create-checkout-session", [StripeController::class, "createCheckoutSession"]);
    Route::get("/stripe/checkout/success", [StripeController::class, "handleCheckoutSuccess"]);
    Route::post("/stripe/subscription/cancel", [StripeController::class, "cancelSubscription"]);
    Route::post("/stripe/payment-method", [StripeController::class, "updatePaymentMethod"]);
    Route::get("/stripe/payment-methods", [StripeController::class, "getPaymentMethods"]);
    Route::get("/stripe/subscription/status", [StripeController::class, "getSubscriptionStatus"]);

    // Extension routes
    Route::get("/extension/status", [ExtensionController::class, "getStatus"]);
    Route::post("/extension/toggle", [ExtensionController::class, "toggleConnection"]);
    Route::post("/extension/features", [ExtensionController::class, "updateFeature"]);
    Route::post("/extension/settings", [ExtensionController::class, "updateSetting"]);

    // User route
    Route::get("/user", function (Request $request) {
        return $request->user();
    });

});

// Note: The /user route was moved inside the auth:sanctum group for consistency


