<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_the_landing_page_is_available(): void
    {
        $this->withoutVite();

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('id="root"', false)
            ->assertSee('data-booking-endpoint="/reservas"', false);
    }
}
