<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $name = (string) config('app.name');
        $shortName = (string) config('app.short_name');

        return response()->json([
            'name' => $name,
            'short_name' => $shortName,
            'description' => (string) config('app.description'),
            'lang' => 'es-ES',
            'id' => '/',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'display_override' => ['window-controls-overlay', 'standalone', 'minimal-ui'],
            'orientation' => 'portrait-primary',
            'theme_color' => '#171512',
            'background_color' => '#f7f4ef',
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                [
                    'name' => 'Reservar cita',
                    'short_name' => 'Reservar',
                    'url' => '/#reservas',
                    'icons' => [['src' => '/icons/icon-192.png', 'sizes' => '192x192']],
                ],
                [
                    'name' => 'Mis citas',
                    'short_name' => 'Mis citas',
                    'url' => '/mis-citas',
                    'icons' => [['src' => '/icons/icon-192.png', 'sizes' => '192x192']],
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
