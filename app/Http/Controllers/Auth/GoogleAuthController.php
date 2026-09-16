<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return to_route('landing')->with('auth_error', 'No se pudo completar el inicio de sesión con Google. Inténtalo de nuevo.');
        }

        $email = mb_strtolower(trim((string) $googleUser->getEmail()));
        if ($email === '') {
            return to_route('landing')->with('auth_error', 'Google no proporcionó una dirección de correo válida.');
        }

        /** @var User|null $user */
        $user = User::query()->where('google_id', $googleUser->getId())->first()
            ?? User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'name' => $user->name ?: ($googleUser->getName() ?: Str::before($email, '@')),
                'avatar_url' => $googleUser->getAvatar() ?: $user->avatar_url,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'avatar_url' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(32)),
            ]);
        }

        Auth::guard('web')->login($user, remember: true);

        $intended = session()->pull('url.intended');
        if ($intended && (Str::startsWith($intended, url('/')) || (Str::startsWith($intended, '/') && ! Str::startsWith($intended, '//')))) {
            return redirect()->to($intended)->with('auth_success', 'Has iniciado sesión correctamente.');
        }

        return to_route('landing')->with('auth_success', 'Has iniciado sesión correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('landing')->with('auth_success', 'Has cerrado sesión correctamente.');
    }
}
