<?php

namespace Tests\Feature;

use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_page_is_available(): void
    {
        $this->withoutVite();

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('id="root"', false)
            ->assertSee('data-booking-endpoint="/reservas"', false);
    }

    public function test_each_mobile_app_screen_has_its_own_url(): void
    {
        $this->withoutVite();

        foreach (['servicios', 'equipo', 'reservar', 'galeria', 'cuenta'] as $view) {
            $this->get("/{$view}")
                ->assertOk()
                ->assertSee("data-mobile-view=\"{$view}\"", false);
        }
    }

    public function test_the_authenticated_google_user_is_exposed_to_the_frontend(): void
    {
        $this->withoutVite();
        $user = User::factory()->create([
            'name' => 'Ana López',
            'email' => 'ana@example.com',
            'avatar_url' => 'https://example.com/ana.jpg',
        ]);

        $this->actingAs($user)
            ->get(route('landing'))
            ->assertOk()
            ->assertSee('&quot;name&quot;:&quot;Ana L\\u00f3pez&quot;', false)
            ->assertSee('&quot;email&quot;:&quot;ana@example.com&quot;', false);
    }

    public function test_the_booking_catalog_reflects_current_service_professional_and_assignment_data(): void
    {
        $this->withoutVite();
        $service = Service::query()->create([
            'slug' => 'corte-dinamico',
            'name' => 'Corte inicial',
            'description' => 'Descripción inicial',
            'duration_minutes' => 30,
            'price_from' => 20,
            'is_active' => true,
        ]);
        $professional = Professional::query()->create([
            'slug' => 'ana-dinamica',
            'name' => 'Ana Inicial',
            'role' => 'Estilista',
            'is_active' => true,
        ]);
        $professional->services()->attach($service);

        $firstCatalog = $this->get(route('landing'))->assertOk()->viewData('bookingCatalog');
        $firstService = collect($firstCatalog['services'])->firstWhere('id', 'corte-dinamico');
        $firstProfessional = collect($firstCatalog['professionals'])->firstWhere('id', 'ana-dinamica');

        $this->assertSame('Descripción inicial', $firstService['description']);
        $this->assertSame(['corte-dinamico'], $firstProfessional['serviceIds']);

        $service->update([
            'name' => 'Corte actualizado',
            'description' => 'Descripción actualizada desde Filament',
            'duration_minutes' => 75,
            'price_from' => 49,
        ]);
        $professional->update(['name' => 'Ana Actualizada', 'role' => 'Directora creativa']);

        $updatedCatalog = $this->get(route('landing'))->assertOk()->viewData('bookingCatalog');
        $updatedService = collect($updatedCatalog['services'])->firstWhere('id', 'corte-dinamico');
        $updatedProfessional = collect($updatedCatalog['professionals'])->firstWhere('id', 'ana-dinamica');

        $this->assertSame('Corte actualizado', $updatedService['name']);
        $this->assertSame('Descripción actualizada desde Filament', $updatedService['description']);
        $this->assertSame(75, $updatedService['durationMinutes']);
        $this->assertSame(49.0, $updatedService['priceFrom']);
        $this->assertSame('Ana Actualizada', $updatedProfessional['name']);
        $this->assertSame('Directora creativa', $updatedProfessional['role']);
    }
}
