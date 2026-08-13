<?php

namespace App\Http\Controllers;

use App\Services\BookingCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function __invoke(Request $request, BookingCatalog $bookingCatalog): View
    {
        $user = $request->user();

        return view('landing', [
            'currentUser' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatarUrl' => $user->avatar_url,
                'isAdmin' => $user->isPanelAdmin(),
            ] : null,
            'bookingCatalog' => $bookingCatalog->get(),
            'mobileView' => $request->route('mobileView', 'inicio'),
            'authMessage' => session('auth_success') ?? session('auth_error'),
            'authMessageType' => session()->has('auth_error') ? 'error' : 'success',
        ]);
    }
}
