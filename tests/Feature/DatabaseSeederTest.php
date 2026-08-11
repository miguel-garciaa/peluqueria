<?php

namespace Tests\Feature;

use App\Models\Professional;
use App\Models\Schedule;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_production_catalog_is_seeded_without_user_factories(): void
    {
        $this->seed();

        $this->assertDatabaseCount('services', 7);
        $this->assertDatabaseCount('professionals', 4);
        $this->assertDatabaseCount('professional_schedules', 24);
        $this->assertSame(7, Service::query()->active()->count());
        $this->assertSame(4, Professional::query()->active()->count());
        $this->assertSame(24, Schedule::query()->where('is_active', true)->count());
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
