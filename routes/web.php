<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController; // Import ResetPasswordController
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\CheckoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- Authentication Routes (Manually Defined to serve SPA) ---

// Explicitly handle GET /login to serve the SPA view
Route::get("/login", function () {
    return view("spa");
})->name("login");

// Registration Routes
Route::get("register", function () { // Serve SPA for GET request
    return view("spa");
})->name("register");
// POST route for registration is handled by API (/api/register)
// Route::post("register", [RegisterController::class, "register"]); // Keep commented or remove if API is sole handler

// Password Reset Routes
Route::get("password/reset", function () { // Serve SPA for GET request
    return view("spa");
})->name("password.request");
// POST route for sending reset link is handled by API (/api/password/email)
// Route::post("password/email", [ForgotPasswordController::class, "sendResetLinkEmail"])->name("password.email"); // Keep commented or remove

Route::get("password/reset/{token}", function () { // Serve SPA for GET request, token handled by Vue router
    return view("spa");
})->name("password.reset");
// POST route for resetting password is handled by API (/api/password/reset)
// Route::post("password/reset", [ResetPasswordController::class, "reset"])->name("password.update"); // Keep commented or remove

// Email Verification Routes
Route::get("email/verify", function () { // Serve SPA for GET request
    return view("spa");
})->middleware("auth:sanctum")->name("verification.notice"); // Keep auth middleware

// Actual verification link click (still handled by Laravel backend)
Route::get("email/verify/{id}/{hash}", [VerificationController::class, "verify"])->middleware(["auth:sanctum", "signed", "throttle:6,1"])->name("verification.verify");

// POST route for resending verification is handled by API (/api/email/resend)
// Route::post("email/resend", [VerificationController::class, "resend"])->middleware(["auth", "throttle:6,1"])->name("verification.resend"); // Keep commented or remove

// --- Other Web Routes ---

// Google Calendar OAuth Routes
Route::get("google/redirect", [GoogleCalendarController::class, "redirectToGoogle"])->name("google.redirect");
Route::get("google/callback", [GoogleCalendarController::class, "handleGoogleCallback"])->name("google.callback");

// Stripe Webhook
Route::post("stripe/webhook", [StripeController::class, "handleWebhook"])->name("stripe.webhook");

// Checkout Routes
Route::middleware(["auth:sanctum"])->group(function () { // Use sanctum guard here too
    Route::post("checkout", [CheckoutController::class, "checkout"])->name("checkout.checkout");
    Route::get("checkout/success", [CheckoutController::class, "success"])->name("checkout.success");
    Route::get("checkout/cancel", [CheckoutController::class, "cancel"])->name("checkout.cancel");
    Route::post("checkout/cancel-subscription", [CheckoutController::class, "cancelSubscription"])->name("checkout.cancel-subscription");
    Route::post("checkout/create-portal-session", [CheckoutController::class, "createPortalSession"])->name("checkout.portal");
});

// Home page route
Route::get("/", function () {
    return view("welcome");
})->name("home");

// --- SPA Catch-all Route (MUST be last) ---
Route::get("/{any?}", function () {
    return view("spa");
})->where("any", "^(?!$).*"); // Regex excludes root path


