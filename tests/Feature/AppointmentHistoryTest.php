<?php

namespace Tests\Feature;

use App\Filament\Pages\AppointmentHistory;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentHistoryPdf;
use App\Services\AppointmentHistoryReport;
use App\Services\CompleteElapsedAppointments;
use App\Support\AppointmentHistoryPeriod;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_elapsed_active_appointments_are_completed_automatically(): void
    {
        $now = CarbonImmutable::parse('2026-08-12 15:00:00', 'UTC');
        CarbonImmutable::setTestNow($now);
        [$user, $service, $professional] = $this->catalog();

        $elapsed = $this->appointment($user, $service, $professional, $now->subHours(2), 'confirmed');
        $future = $this->appointment($user, $service, $professional, $now->addHour(), 'confirmed');
        $cancelled = $this->appointment($user, $service, $professional, $now->subHours(3), 'cancelled');

        $this->assertSame(1, app(CompleteElapsedAppointments::class)->handle($now));

        $elapsed->refresh();
        $this->assertSame('completed', $elapsed->status);
        $this->assertTrue($elapsed->completed_at->equalTo($elapsed->ends_at));
        $this->assertSame('confirmed', $future->fresh()->status);
        $this->assertSame('cancelled', $cancelled->fresh()->status);
        $this->assertNull($cancelled->fresh()->completed_at);
    }

    public function test_the_completion_command_is_idempotent(): void
    {
        $now = CarbonImmutable::parse('2026-08-12 15:00:00', 'UTC');
        CarbonImmutable::setTestNow($now);
        [$user, $service, $professional] = $this->catalog();
        $this->appointment($user, $service, $professional, $now->subHours(2), 'pending');

        $this->artisan('appointments:complete')
            ->expectsOutputToContain('Citas completadas: 1')
            ->assertSuccessful();
        $this->artisan('appointments:complete')
            ->expectsOutputToContain('Citas completadas: 0')
            ->assertSuccessful();
    }

    public function test_history_periods_only_contain_completed_non_cancelled_appointments(): void
    {
        $now = CarbonImmutable::parse('2026-08-12 15:00:00', 'UTC');
        [$user, $service, $professional] = $this->catalog();

        $recent = $this->appointment($user, $service, $professional, $now->subHours(3), 'completed');
        $old = $this->appointment($user, $service, $professional, $now->subMonths(4), 'completed');
        $this->appointment($user, $service, $professional, $now->subHours(4), 'cancelled');

        $report = app(AppointmentHistoryReport::class);

        $this->assertEquals(
            [$recent->id],
            $report->query(AppointmentHistoryPeriod::Month, $now)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$recent->id, $old->id],
            $report->query(AppointmentHistoryPeriod::All, $now)->pluck('id')->all(),
        );
    }

    public function test_an_administrator_can_see_the_completed_history_without_cancelled_bookings(): void
    {
        $now = CarbonImmutable::parse('2026-08-12 15:00:00', 'UTC');
        CarbonImmutable::setTestNow($now);
        [$user, $service, $professional] = $this->catalog();
        $admin = User::factory()->create(['is_admin' => true]);
        $completed = $this->appointment($user, $service, $professional, $now->subHours(3), 'completed');
        $cancelled = $this->appointment($user, $service, $professional, $now->subHours(4), 'cancelled');

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(AppointmentHistory::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$completed])
            ->assertCanNotSeeTableRecords([$cancelled])
            ->assertSee('Solo se muestran citas completadas');
    }

    public function test_the_history_pdf_is_generated_with_the_selected_period(): void
    {
        $now = CarbonImmutable::parse('2026-08-12 15:00:00', config('app.business_timezone'));
        [$user, $service, $professional] = $this->catalog();
        $appointment = $this->appointment($user, $service, $professional, $now->utc()->subHours(3), 'completed');
        $appointment->load(['user', 'service', 'professional']);

        $pdf = app(AppointmentHistoryPdf::class)->render(
            collect([$appointment]),
            AppointmentHistoryPeriod::Week,
            $now,
        );

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(5000, strlen($pdf));
    }

    public function test_an_administrator_can_download_the_history_pdf(): void
    {
        $now = CarbonImmutable::parse('2026-08-12 15:00:00', config('app.business_timezone'));
        CarbonImmutable::setTestNow($now);
        [$user, $service, $professional] = $this->catalog();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->appointment($user, $service, $professional, $now->utc()->subHours(3), 'completed');

        $response = $this->actingAs($admin)->get(route('appointment-history.pdf', [
            'period' => AppointmentHistoryPeriod::Week->value,
        ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('historial-citas-ultima-semana-2026-08-12.pdf');
        $this->assertStringStartsWith('%PDF-', $response->streamedContent());
    }

    /** @return array{User, Service, Professional} */
    private function catalog(): array
    {
        $user = User::factory()->create([
            'name' => 'Cliente Historial',
            'email' => 'historial@example.com',
            'phone' => '+34 600 11 22 33',
        ]);
        $service = Service::query()->create([
            'slug' => 'historial-servicio',
            'name' => 'Corte historial',
            'duration_minutes' => 60,
            'price_from' => 35,
            'is_active' => true,
        ]);
        $professional = Professional::query()->create([
            'slug' => 'historial-profesional',
            'name' => 'Laura Historial',
            'is_active' => true,
        ]);

        return [$user, $service, $professional];
    }

    private function appointment(
        User $user,
        Service $service,
        Professional $professional,
        CarbonImmutable $startsAt,
        string $status,
    ): Appointment {
        $endsAt = $startsAt->addHour();

        return Appointment::query()->forceCreate([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $status,
            'cancelled_at' => $status === 'cancelled' ? $endsAt : null,
            'completed_at' => $status === 'completed' ? $endsAt : null,
        ]);
    }
}
