<?php

use App\Http\Controllers\AppointmentRequestController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('landing');
Route::post('/reservas', [AppointmentRequestController::class, 'store'])->name('bookings.store');
