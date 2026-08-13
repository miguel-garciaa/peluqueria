<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless($this->isConfigured(), 503, 'Las notificaciones push todavía no están configuradas.');

        $data = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:500'],
            'keys.p256dh' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+={0,2}$/'],
            'keys.auth' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+={0,2}$/'],
            'contentEncoding' => ['nullable', 'in:aes128gcm,aesgcm'],
        ]);

        $this->ensureAllowedEndpoint($data['endpoint']);

        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            $data['contentEncoding'] ?? 'aes128gcm',
        );

        return response()->json(['message' => 'Notificaciones activadas.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:500'],
        ]);

        $request->user()->deletePushSubscription($data['endpoint']);

        return response()->json(['message' => 'Notificaciones desactivadas.']);
    }

    private function isConfigured(): bool
    {
        return filled(config('webpush.vapid.public_key')) && filled(config('webpush.vapid.private_key'));
    }

    private function ensureAllowedEndpoint(string $endpoint): void
    {
        $host = Str::lower((string) parse_url($endpoint, PHP_URL_HOST));
        $allowed = collect(config('webpush.allowed_hosts', []))
            ->contains(fn (string $pattern): bool => Str::is(Str::lower($pattern), $host));

        if (! $allowed) {
            throw ValidationException::withMessages([
                'endpoint' => 'El proveedor de notificaciones no es compatible.',
            ]);
        }
    }
}
