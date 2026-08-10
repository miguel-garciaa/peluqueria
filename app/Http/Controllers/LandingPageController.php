<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('landing', [
            'currentUser' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
                'avatarUrl' => $user->avatar_url,
            ] : null,
            'authMessage' => session('auth_success') ?? session('auth_error'),
            'authMessageType' => session()->has('auth_error') ? 'error' : 'success',
        ]);
    }
}
