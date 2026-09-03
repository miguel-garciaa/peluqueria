<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate;
use Illuminate\Http\Request;

class AuthenticateAdmin extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        if ($request instanceof Request && ! $request->expectsJson()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return route('google.redirect');
    }
}
