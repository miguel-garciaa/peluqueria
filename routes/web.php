<?php

use App\Http\Controllers\AppointmentRequestController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('landing');
Route::post('/reservas', [AppointmentRequestController::class, 'store'])->name('bookings.store');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::post('/logout', [GoogleAuthController::class, 'logout'])->middleware('auth')->name('logout');
