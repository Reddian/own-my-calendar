<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken; // Import VerifyCsrfToken
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController; // Import RegisterController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import ForgotPasswordController
use App\Http\Controllers\Auth\ResetPasswordController; // Import ResetPasswordController
use App\Http\Controllers\Auth\VerificationController; // Import VerificationController
use App\Http\Controllers\UserProfileController; // Keep for potential other uses
use App\Http\Controllers\ProfileController; // Import the new ProfileController
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
    Route::post("/email/resend", [VerificationController::class, "resendApi"])->name("verification.resend.api"); // Add API resend verification route

    // User profile routes (NEW - Using ProfileController)
    Route::put("/profile", [ProfileController::class, "update"]); // Update name/email
    Route::put("/password", [ProfileController::class, "updatePassword"]); // Update password

    // User route (Get current user)
    Route::get("/user", function (Request $request) {
        return $request->user();
    });

    // Onboarding routes
    Route::get("/onboarding", [OnboardingController::class, "index"]);
    Route::post("/onboarding", [OnboardingController::class, "store"]);

    // Calendar grade routes
    Route::get("/grades", [CalendarGradeController::class, "index"]);
    Route::post("/grades", [CalendarGradeController::class, "store"]);
    Route::get("/grades/current-week", [CalendarGradeController::class, "getCurrentWeekGrade"]);
    Route::get("/grades/{id}", [CalendarGradeController::class, "show"]);
    Route::post("/grades/date-range", [CalendarGradeController::class, "getGradesByDateRange"]);

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

    // Notification Settings (Assuming a NotificationController exists or needs creation)
    // Route::get("/notifications/settings", [NotificationController::class, "getSettings"]);
    // Route::put("/notifications/settings", [NotificationController::class, "updateSettings"]);

});

// Note: Removed old /profile routes that used UserProfileController
// Note: Added PUT routes for /profile and /password using ProfileController

