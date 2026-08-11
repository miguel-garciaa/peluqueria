<?php

use App\Http\Controllers\AppointmentAvailabilityController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)
    ->middleware('throttle:landing')
    ->name('landing');
Route::middleware('throttle:oauth')->group(function (): void {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});
Route::get('/login', fn () => to_route('google.redirect'))->name('login');

Route::middleware('auth')->group(function (): void {
    Route::get('/reservas/disponibilidad', AppointmentAvailabilityController::class)
        ->middleware('throttle:availability')
        ->name('bookings.availability');
    Route::post('/reservas', [AppointmentController::class, 'store'])
        ->middleware('throttle:booking')
        ->name('bookings.store');
    Route::get('/mis-citas', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('/mis-citas/{reference}/anular', [AppointmentController::class, 'cancel'])
        ->middleware('throttle:cancellation')
        ->name('appointments.cancel');
    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');
});
