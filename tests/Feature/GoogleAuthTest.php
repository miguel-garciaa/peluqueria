<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_start_google_authentication(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('with')
            ->once()
            ->with(['prompt' => 'select_account'])
            ->andReturnSelf();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/v2/auth'));
        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
    }

    public function test_verified_google_users_are_created_and_authenticated(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'name' => 'María García',
            'email' => 'MARIA@example.com',
            'avatar' => 'https://example.com/maria.jpg',
            'email_verified' => true,
        ]));

        $this->get(route('google.callback'))
            ->assertRedirect(route('landing'))
            ->assertSessionHas('auth_success');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'google_id' => 'google-123',
            'name' => 'María García',
            'email' => 'maria@example.com',
            'avatar_url' => 'https://example.com/maria.jpg',
        ]);
    }

    public function test_google_login_links_an_existing_verified_email_without_duplication(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'cliente@example.com',
            'email_verified_at' => null,
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-existing',
            'name' => 'Cliente Google',
            'email' => 'cliente@example.com',
            'email_verified' => true,
        ]));

        $this->get(route('google.callback'))->assertRedirect(route('landing'));

        $this->assertAuthenticatedAs($existingUser->fresh());
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'google_id' => 'google-existing',
            'email' => 'cliente@example.com',
        ]);
        $this->assertNotNull($existingUser->fresh()->email_verified_at);
    }

    public function test_unverified_google_emails_are_rejected(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-unverified',
            'email' => 'unverified@example.com',
            'email_verified' => false,
        ]));

        $this->get(route('google.callback'))
            ->assertRedirect(route('landing'))
            ->assertSessionHas('auth_error');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_authenticated_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('landing'));

        $this->assertGuest();
    }
}
