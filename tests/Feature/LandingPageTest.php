<?php

namespace Tests\Feature;

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
}
