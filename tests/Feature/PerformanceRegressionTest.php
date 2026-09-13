<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Services\AppointmentAvailability;
use App\Services\BookingCatalog;
use App\Services\CompleteElapsedAppointments;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\CreatesBookingData;
use Tests\TestCase;

#[Group('performance')]
class PerformanceRegressionTest extends TestCase
{
    use CreatesBookingData;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_availability_has_a_constant_query_budget_with_many_bookings(): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [$user, $service, $professional] = $this->bookingCatalog();
        $this->schedule($professional, startsAt: '08:00', endsAt: '20:00', interval: 15);
        $targetDay = CarbonImmutable::parse('2026-09-14', 'Europe/Madrid');

        foreach (range(1, 150) as $index) {
            $day = $targetDay->addDays($index);
            $this->appointment($user, $service, $professional, $day->setTime(9, 0));
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $startedAt = hrtime(true);
        $slots = app(AppointmentAvailability::class)->slots($targetDay, $service, $professional);
        $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertNotEmpty($slots);
        $this->assertLessThanOrEqual(6, count($queries), 'La disponibilidad ha superado su presupuesto de consultas.');
        $this->assertLessThan(1000, $elapsedMilliseconds, 'La disponibilidad ha tardado más de un segundo.');
    }

    public function test_booking_catalog_avoids_n_plus_one_queries_at_scale(): void
    {
        foreach (range(1, 30) as $index) {
            [, $service, $professional] = $this->bookingCatalog(
                ['slug' => "service-{$index}", 'name' => "Servicio {$index}"],
                ['slug' => "professional-{$index}", 'name' => "Profesional {$index}"],
            );
            $professional->services()->syncWithoutDetaching([$service->id]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $startedAt = hrtime(true);
        $catalog = app(BookingCatalog::class)->get();
        $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(30, $catalog['services']);
        $this->assertCount(30, $catalog['professionals']);
        $this->assertLessThanOrEqual(4, $queryCount, 'El catálogo contiene una regresión N+1.');
        $this->assertLessThan(1000, $elapsedMilliseconds, 'El catálogo ha tardado más de un segundo.');
    }

    public function test_bulk_completion_is_chunked_and_idempotent(): void
    {
        $now = CarbonImmutable::parse('2026-09-10 12:00:00 UTC');
        CarbonImmutable::setTestNow($now);
        [$user, $service, $professional] = $this->bookingCatalog();

        foreach (range(1, 205) as $index) {
            $this->appointment(
                $user,
                $service,
                $professional,
                $now->subMinutes($index + 61),
            );
        }

        $startedAt = hrtime(true);
        $completed = app(CompleteElapsedAppointments::class)->handle($now);
        $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;

        $this->assertSame(205, $completed);
        $this->assertSame(205, Appointment::query()->where('status', 'completed')->count());
        $this->assertDatabaseCount('payments', 205);
        $this->assertSame(0, app(CompleteElapsedAppointments::class)->handle($now));
        $this->assertLessThan(4000, $elapsedMilliseconds, 'El cierre masivo ha superado cuatro segundos.');
    }
}
