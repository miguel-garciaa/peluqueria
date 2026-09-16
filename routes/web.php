<?php

use App\Http\Controllers\AppointmentAvailabilityController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentHistoryPdfController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)
    ->middleware('throttle:landing')
    ->name('landing');

Route::get('/login', [GoogleAuthController::class, 'redirect'])->name('login');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::match(['get', 'post'], '/logout', [GoogleAuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/historial-citas/exportar.pdf', AppointmentHistoryPdfController::class)
        ->name('appointment-history.pdf');
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
});
