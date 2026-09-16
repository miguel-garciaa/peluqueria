<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookingData;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use CreatesBookingData;
    use RefreshDatabase;

    public function test_non_admin_cannot_export_private_appointment_history(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('appointment-history.pdf', ['period' => 'all']))
            ->assertForbidden();
    }

    public function test_pdf_export_rejects_invalid_filters_before_querying_history(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->getJson(route('appointment-history.pdf', [
            'period' => 'arbitrary',
            'service' => PHP_INT_MAX,
            'professional' => PHP_INT_MAX,
        ]))->assertUnprocessable()->assertJsonValidationErrors(['period', 'service', 'professional']);
    }

    public function test_production_https_responses_enable_hsts_but_http_does_not(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
        $this->get('https://localhost/')->assertHeader(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains',
        );
    }

    public function test_json_and_private_routes_receive_no_sniff_and_no_store_headers(): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [$user, $service, $professional] = $this->bookingCatalog();
        $this->schedule($professional);

        $response = $this->actingAs($user)->getJson(route('bookings.availability', [
            'date' => '2026-09-14',
            'service' => $service->slug,
            'professional' => $professional->slug,
        ]))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_reference_lookup_does_not_leak_other_users_appointments(): void
    {
        [$owner, $service, $professional] = $this->bookingCatalog();
        $attacker = User::factory()->create();
        $appointment = $this->appointment($owner, $service, $professional, CarbonImmutable::now()->addDay());

        $this->actingAs($attacker)
            ->patch(route('appointments.cancel', $appointment->reference))
            ->assertNotFound();

        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_sensitive_model_fields_never_appear_in_json(): void
    {
        $user = User::factory()->create([
            'password' => 'super-secret-password',
            'remember_token' => 'remember-secret',
        ]);

        $serialized = $user->toArray();
        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
    }
}
