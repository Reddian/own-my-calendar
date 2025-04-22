<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\MultiCalendarController;
use App\Http\Controllers\CalendarGradeController;
use App\Http\Controllers\AIGradingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\NotificationController;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::get('/extension', [DashboardController::class, 'extension'])->name('extension');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::get('/subscription', [DashboardController::class, 'subscription'])->name('subscription');
    
    // Account settings routes
    Route::put('/account/update', [AccountController::class, 'updateAccount'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    
    // Notification settings routes
    Route::put('/notifications/update', [NotificationController::class, 'updateSettings'])->name('notifications.update');
    
    // Checkout routes
    Route::post('/checkout/create-session', [CheckoutController::class, 'createCheckoutSession'])->name('checkout.create-session');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/cancel-subscription', [CheckoutController::class, 'cancelSubscription'])->name('checkout.cancel-subscription');
});

// Terms and Privacy routes
Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Stripe webhook route
Route::post('/stripe/webhook', [CheckoutController::class, 'handleWebhook'])->name('stripe.webhook');
