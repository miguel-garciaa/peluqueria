<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesBookingData;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use CreatesBookingData;
    use RefreshDatabase;

    #[DataProvider('unsafeGoogleProfiles')]
    public function test_google_callback_rejects_untrusted_identity_profiles(array $profile): void
    {
        Socialite::fake('google', SocialiteUser::fake($profile));

        $this->get(route('google.callback'))
            ->assertRedirect(route('landing'))
            ->assertSessionHas('auth_error');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public static function unsafeGoogleProfiles(): array
    {
        return [
            'missing provider id' => [['id' => '', 'email' => 'safe@example.com', 'email_verified' => true]],
            'oversized provider id' => [['id' => str_repeat('x', 256), 'email' => 'safe@example.com', 'email_verified' => true]],
            'invalid email' => [['id' => 'google-safe', 'email' => 'javascript:alert(1)', 'email_verified' => true]],
            'oversized email' => [['id' => 'google-safe', 'email' => str_repeat('a', 250).'@x.com', 'email_verified' => true]],
            'unverified email' => [['id' => 'google-safe', 'email' => 'safe@example.com', 'email_verified' => false]],
        ];
    }

    public function test_google_callback_refuses_account_takeover_when_email_is_linked_elsewhere(): void
    {
        $user = User::factory()->create([
            'email' => 'victim@example.com',
            'google_id' => 'original-google-id',
        ]);
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'attacker-google-id',
            'email' => 'victim@example.com',
            'email_verified' => true,
        ]));

        $this->get(route('google.callback'))
            ->assertRedirect(route('landing'))
            ->assertSessionHas('auth_error');

        $this->assertGuest();
        $this->assertSame('original-google-id', $user->fresh()->google_id);
    }

    public function test_google_profile_text_is_bounded_and_empty_names_get_a_safe_fallback(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-bounded',
            'name' => '',
            'email' => 'fallback@example.com',
            'avatar' => 'https://example.com/'.str_repeat('a', 3000),
            'email_verified' => true,
        ]));

        $this->get(route('google.callback'))->assertRedirect(route('landing'));

        $user = User::query()->sole();
        $this->assertSame('fallback', $user->name);
        $this->assertLessThanOrEqual(2048, mb_strlen((string) $user->avatar_url));
        $this->assertTrue(Hash::needsRehash($user->password) || $user->password !== '');
    }

    public function test_provider_errors_never_create_or_authenticate_a_user(): void
    {
        $this->get(route('google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('landing'))
            ->assertSessionHas('auth_error');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

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
