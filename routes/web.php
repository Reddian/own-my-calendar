<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
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

// --- Authentication Routes (Manually Defined) ---

// Explicitly handle GET /login to serve the SPA view
Route::get("/login", function () {
    return view("spa");
})->name("login");

// Registration Routes (Assuming standard Blade views for now)
Route::get("register", [RegisterController::class, "showRegistrationForm"])->name("register");
Route::post("register", [RegisterController::class, "register"]);

// Password Reset Routes (Assuming standard Blade views for now)
Route::get("password/reset", [ForgotPasswordController::class, "showLinkRequestForm"])->name("password.request");
Route::post("password/email", [ForgotPasswordController::class, "sendResetLinkEmail"])->name("password.email");
Route::get("password/reset/{token}", [ForgotPasswordController::class, "showResetForm"])->name("password.reset"); // Changed controller
Route::post("password/reset", [ForgotPasswordController::class, "reset"])->name("password.update"); // Changed controller

// Email Verification Routes (Assuming standard Blade views for now)
Route::get("email/verify", [VerificationController::class, "show"])->name("verification.notice");
Route::get("email/verify/{id}/{hash}", [VerificationController::class, "verify"])->middleware(["signed", "throttle:6,1"])->name("verification.verify");
Route::post("email/resend", [VerificationController::class, "resend"])->middleware(["auth", "throttle:6,1"])->name("verification.resend");

// --- Other Web Routes ---

// Google Calendar OAuth Routes
Route::get("google/redirect", [GoogleCalendarController::class, "redirectToGoogle"])->name("google.redirect");
Route::get("google/callback", [GoogleCalendarController::class, "handleGoogleCallback"])->name("google.callback");

// Stripe Webhook
Route::post("stripe/webhook", [StripeController::class, "handleWebhook"])->name("stripe.webhook");

// Checkout Routes
Route::middleware(["auth"])->group(function () {
    Route::post("checkout", [CheckoutController::class, "checkout"])->name("checkout.checkout");
    Route::get("checkout/success", [CheckoutController::class, "success"])->name("checkout.success");
    Route::get("checkout/cancel", [CheckoutController::class, "cancel"])->name("checkout.cancel");
    Route::post("checkout/cancel-subscription", [CheckoutController::class, "cancelSubscription"])->name("checkout.cancel-subscription");
    Route::post("checkout/create-portal-session", [CheckoutController::class, "createPortalSession"])->name("checkout.portal");
});

// --- SPA Catch-all Route (MUST be last) ---
Route::get("/{any?}", function () {
    return view("spa");
})->where("any", ".*");


