<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\Service;
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
                'phone' => $user->phone,
                'avatarUrl' => $user->avatar_url,
                'isAdmin' => $user->is_admin,
            ] : null,
            'bookingCatalog' => [
                'services' => Service::query()->active()->orderBy('id')->get()->map(fn (Service $service) => [
                    'id' => $service->slug,
                    'name' => $service->name,
                    'durationMinutes' => $service->duration_minutes,
                    'priceFrom' => $service->price_from !== null ? (float) $service->price_from : null,
                    'isCustom' => $service->is_custom,
                ]),
                'professionals' => Professional::query()->active()->orderBy('id')->get()->map(fn (Professional $professional) => [
                    'id' => $professional->slug,
                    'name' => $professional->name,
                    'role' => $professional->role,
                ]),
            ],
            'authMessage' => session('auth_success') ?? session('auth_error'),
            'authMessageType' => session()->has('auth_error') ? 'error' : 'success',
        ]);
    }
}
