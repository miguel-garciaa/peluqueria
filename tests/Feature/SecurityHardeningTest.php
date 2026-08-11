<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_include_defensive_security_headers(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=(), payment=(), usb=()')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_authenticated_responses_are_not_stored_by_browsers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('landing'))->assertOk();

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
    }

    public function test_sensitive_routes_have_specific_rate_limiters(): void
    {
        $this->assertContains('throttle:oauth', Route::getRoutes()->getByName('google.redirect')->gatherMiddleware());
        $this->assertContains('throttle:oauth', Route::getRoutes()->getByName('google.callback')->gatherMiddleware());
        $this->assertContains('throttle:availability', Route::getRoutes()->getByName('bookings.availability')->gatherMiddleware());
        $this->assertContains('throttle:booking', Route::getRoutes()->getByName('bookings.store')->gatherMiddleware());
        $this->assertContains('throttle:cancellation', Route::getRoutes()->getByName('appointments.cancel')->gatherMiddleware());
    }

    public function test_booking_rate_limits_use_independent_redis_counters(): void
    {
        $limiter = RateLimiter::limiter('booking');
        $limits = $limiter(Request::create('/reservas', 'POST', server: ['REMOTE_ADDR' => '127.0.0.1']));

        $this->assertCount(2, $limits);
        $this->assertNotSame($limits[0]->key, $limits[1]->key);
        $this->assertSame(5, $limits[0]->maxAttempts);
        $this->assertSame(20, $limits[1]->maxAttempts);
    }

    public function test_redis_queue_and_admin_writes_use_safe_transaction_defaults(): void
    {
        $this->assertTrue((bool) config('queue.connections.redis.after_commit'));
        $this->assertSame(5, config('queue.connections.redis.block_for'));
        $this->assertSame(120, config('queue.connections.redis.retry_after'));
        $this->assertTrue(Filament::getPanel('admin')->hasDatabaseTransactions());
    }
}
