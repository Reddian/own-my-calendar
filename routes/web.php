<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
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

// Authentication Routes (Disable default login routes, keep others)
Auth::routes(["login" => false, "register" => true, "reset" => true, "verify" => true]);

// Google Calendar OAuth Routes (Keep for backend integration)
Route::get("google/redirect", [GoogleCalendarController::class, "redirectToGoogle"])->name("google.redirect");
Route::get("google/callback", [GoogleCalendarController::class, "handleGoogleCallback"])->name("google.callback");

// Stripe Webhook (Keep for backend integration)
Route::post("stripe/webhook", [StripeController::class, "handleWebhook"])->name("stripe.webhook");

// Checkout Routes (Keep for backend integration)
Route::middleware(["auth"])->group(function () {
    Route::post("checkout", [CheckoutController::class, "checkout"])->name("checkout.checkout");
    Route::get("checkout/success", [CheckoutController::class, "success"])->name("checkout.success");
    Route::get("checkout/cancel", [CheckoutController::class, "cancel"])->name("checkout.cancel");
    Route::post("checkout/cancel-subscription", [CheckoutController::class, "cancelSubscription"])->name("checkout.cancel-subscription");
    Route::post("checkout/create-portal-session", [CheckoutController::class, "createPortalSession"])->name("checkout.portal");
});

// API Routes (These should be in api.php, but ensure they exist for Vue app)
// Example: Route::middleware("auth:sanctum")->get("/api/user", function (Request $request) { return $request->user(); });

// Catch-all route for the Vue SPA
// This route MUST be the last web route defined.
// It serves the base SPA view for any non-API, non-specific web route.
// Authentication is now handled by the Vue router guards.
Route::get("/{any?}", function () {
    return view("spa"); // Return the single blade file that hosts the Vue app
})->where("any", ".*"); // Allows any path, including nested paths


