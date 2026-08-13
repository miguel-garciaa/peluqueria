<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_is_valid_and_uses_the_configured_brand(): void
    {
        config([
            'app.name' => 'Salón Prueba',
            'app.short_name' => 'Salón',
            'app.description' => 'Descripción instalable',
        ]);

        $this->get(route('pwa.manifest'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json; charset=UTF-8')
            ->assertJsonPath('name', 'Salón Prueba')
            ->assertJsonPath('short_name', 'Salón')
            ->assertJsonPath('start_url', '/')
            ->assertJsonPath('scope', '/')
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('shortcuts.0.url', '/reservar')
            ->assertJsonPath('icons.0.sizes', '192x192')
            ->assertJsonPath('icons.1.sizes', '512x512')
            ->assertJsonPath('icons.2.purpose', 'maskable');
    }

    public function test_public_and_private_react_views_include_install_metadata(): void
    {
        $this->withoutVite();

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('apple-mobile-web-app-capable', false);

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('appointments.index'))
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('apple-mobile-web-app-capable', false);
    }

    public function test_service_worker_excludes_private_routes_from_cache_storage(): void
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));

        $this->assertIsString($serviceWorker);
        foreach (['auth', 'login', 'logout', 'reservas', 'mis-citas', 'admin'] as $path) {
            $this->assertStringContainsString("/^\\/$path", $serviceWorker);
        }
        $this->assertStringContainsString('request.method !== "GET" || isPrivateRequest(url)', $serviceWorker);
        $this->assertStringContainsString('request.mode === "navigate"', $serviceWorker);
        $this->assertStringContainsString('caches.match(OFFLINE_URL)', $serviceWorker);
    }
}
