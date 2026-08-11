<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return to_route('landing')->with('auth_error', 'No se pudo completar el acceso con Google.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
            $profile = $googleUser->getRaw();
            $googleId = trim((string) $googleUser->getId());
            $email = Str::lower(trim((string) $googleUser->getEmail()));
            $emailVerified = filter_var(
                $profile['email_verified'] ?? $profile['verified_email'] ?? false,
                FILTER_VALIDATE_BOOL,
            );

            if ($googleId === '' || $email === '' || ! $emailVerified) {
                return to_route('landing')->with('auth_error', 'Google no ha podido verificar este correo electrónico.');
            }

            $user = User::query()
                ->where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            if ($user && $user->google_id && $user->google_id !== $googleId) {
                return to_route('landing')->with('auth_error', 'Este correo ya está vinculado a otra cuenta de Google.');
            }

            if ($user) {
                $user->fill([
                    'google_id' => $googleId,
                    'name' => $googleUser->getName() ?: $user->name,
                    'avatar_url' => $googleUser->getAvatar(),
                ]);
                $user->email_verified_at ??= now();
                $user->save();
            } else {
                $user = User::query()->create([
                    'google_id' => $googleId,
                    'name' => $googleUser->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'email_verified_at' => now(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'password' => Str::random(64),
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('landing'))
                ->with('auth_success', 'Has iniciado sesión con Google.');
        } catch (QueryException $exception) {
            report($exception);

            return to_route('landing')->with('auth_error', 'No se pudo conectar con el servidor. Inténtalo de nuevo más tarde.');
        } catch (Throwable $exception) {
            report($exception);

            return to_route('landing')->with('auth_error', 'No se pudo completar el acceso con Google. Inténtalo de nuevo.');
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('landing');
    }
}
