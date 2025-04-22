<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'home'])->name('dashboard');
Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
Route::get('/history', [DashboardController::class, 'history'])->name('history');
Route::get('/extension', [DashboardController::class, 'extension'])->name('extension');
Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');

// Placeholder routes for footer links
Route::get('/terms', function() {
    return view('terms');
})->name('terms');

Route::get('/privacy', function() {
    return view('privacy');
})->name('privacy');

// Placeholder route for subscription
Route::get('/subscription', function() {
    return view('subscription');
})->name('subscription');
