<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\NotificationController;

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

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
Route::get('/history', [DashboardController::class, 'history'])->name('history');
Route::get('/extension', [DashboardController::class, 'extension'])->name('extension');
Route::get('/settings', [AccountController::class, 'index'])->name('settings');
Route::get('/subscription', [DashboardController::class, 'subscription'])->name('subscription');
Route::get('/terms', [DashboardController::class, 'terms'])->name('terms');
Route::get('/privacy', [DashboardController::class, 'privacy'])->name('privacy');

// Account routes
Route::put('/account/update', [AccountController::class, 'update'])->name('account.update');
Route::put('/account/update-password', [AccountController::class, 'updatePassword'])->name('account.update-password');

// Notification routes
Route::put('/notification/update-settings', [NotificationController::class, 'updateSettings'])->name('notification.update-settings');

// Google Calendar routes
Route::get('/google-calendar/redirect', [GoogleCalendarController::class, 'redirect']);
Route::get('/google-calendar/callback', [GoogleCalendarController::class, 'callback']);
Route::post('/google-calendar/disconnect', [GoogleCalendarController::class, 'disconnect']);
