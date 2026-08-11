<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\BookingTrend;
use App\Filament\Widgets\UpcomingBookings;
use App\Mail\AppointmentCancelled;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use App\Services\ManageAppointment;
use Carbon\CarbonImmutable;
use Filament\Enums\ThemeMode;
use Filament\Facades\Filament;
use Filament\Widgets\AccountWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_duplicate_filament_logout_widget_is_not_registered(): void
    {
        $this->assertNotContains(AccountWidget::class, Filament::getPanel('admin')->getWidgets());
    }

    public function test_the_control_panel_uses_the_baskunana_theme(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertSame('resources/css/filament/admin/theme.css', $panel->getViteTheme());
        $this->assertSame(ThemeMode::Dark, $panel->getDefaultThemeMode());
        $this->assertNull($panel->getGlobalSearchProvider());
    }

    public function test_the_dashboard_theme_keeps_the_table_toolbar_compact(): void
    {
        $theme = file_get_contents(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString(
            '> .fi-ta-actions:first-child:not(:has(*))',
            $theme,
        );
        $this->assertStringContainsString(
            '.fi-wi-chart > .fi-section > .fi-section-content-ctn',
            $theme,
        );
        $this->assertStringContainsString(
            '.fi-wi-table .fi-ta-header {',
            $theme,
        );
    }

    public function test_the_dashboard_content_widgets_share_the_same_grid_row(): void
    {
        $expectedSpan = ['md' => 2, 'xl' => 1];

        $this->assertSame($expectedSpan, app(BookingTrend::class)->getColumnSpan());
        $this->assertSame($expectedSpan, app(UpcomingBookings::class)->getColumnSpan());
    }

    public function test_the_booking_trend_chart_has_a_compact_maximum_height(): void
    {
        Livewire::test(BookingTrend::class)
            ->assertSeeHtml('max-height: 18rem');
    }

    public function test_only_administrators_can_access_the_control_panel(): void
    {
        $this->get('/admin')->assertRedirect(route('google.redirect'));

        $customer = User::factory()->create();
        $this->actingAs($customer)->get('/admin')->assertForbidden();

        $configuredAdmin = User::factory()->create(['email' => 'owner@example.com']);
        config()->set('admin.email', 'OWNER@example.com');
        $this->actingAs($configuredAdmin)
            ->get('/admin')
            ->assertOk();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Reservas activas')
            ->assertSee('Clientes registrados');
    }

    public function test_the_clients_resource_excludes_all_administrator_accounts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $configuredAdmin = User::factory()->create(['email' => 'owner@example.com']);
        $customer = User::factory()->create();
        config()->set('admin.email', 'OWNER@example.com');

        $this->assertEquals(
            [$customer->id],
            UserResource::getEloquentQuery()->pluck('id')->all(),
        );

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee($customer->email)
            ->assertDontSee($admin->email)
            ->assertDontSee($configuredAdmin->email);

        $this->actingAs($admin)
            ->get('/admin/users/'.$configuredAdmin->getRouteKey())
            ->assertNotFound();
    }

    public function test_the_agenda_and_all_management_sections_render_for_an_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach ([
            '/admin/agenda',
            '/admin/appointments',
            '/admin/appointments/create',
            '/admin/users',
            '/admin/professionals',
            '/admin/professionals/create',
            '/admin/services',
            '/admin/services/create',
            '/admin/schedules',
            '/admin/schedules/create',
            '/admin/professional-calendar-entries',
            '/admin/professional-calendar-entries/create',
        ] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_the_agenda_displays_the_complete_appointment_information(): void
    {
        [$user, $service, $professional, $startsAt] = $this->catalog();
        $admin = User::factory()->create(['is_admin' => true]);
        $startsAt = CarbonImmutable::now(config('app.business_timezone'))->addHour()->startOfHour();

        Appointment::query()->forceCreate([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => 'Ana Cliente',
            'customer_phone' => '600 111 222',
            'starts_at' => $startsAt->utc(),
            'ends_at' => $startsAt->addMinutes(45)->utc(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->get('/admin/agenda')
            ->assertOk()
            ->assertSee('Ana Cliente')
            ->assertSee('+34 600 11 12 22')
            ->assertSee($service->name)
            ->assertSee($professional->name)
            ->assertSee('Anular cita');

        $this->actingAs($admin)
            ->get('/admin/appointments')
            ->assertOk()
            ->assertSee('Anular cita');
    }

    public function test_an_administrator_can_cancel_a_client_appointment_once(): void
    {
        Mail::fake();
        [$user, $service, $professional] = $this->catalog();
        $startsAt = CarbonImmutable::now(config('app.business_timezone'))->addDay()->setTime(12, 0);
        $appointment = Appointment::query()->forceCreate([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone,
            'starts_at' => $startsAt->utc(),
            'ends_at' => $startsAt->addMinutes(45)->utc(),
            'status' => 'confirmed',
        ]);

        $this->assertTrue(app(ManageAppointment::class)->cancel($appointment));
        $this->assertSame('cancelled', $appointment->status);
        $this->assertNotNull($appointment->cancelled_at);
        $this->assertFalse(app(ManageAppointment::class)->cancel($appointment));

        Mail::assertQueued(AppointmentCancelled::class, 1);
    }

    public function test_admin_rescheduling_uses_the_same_availability_rules_and_ignores_the_current_booking(): void
    {
        [$user, $service, $professional, $startsAt] = $this->catalog();
        $manager = app(ManageAppointment::class);
        $form = [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $user->name,
            'customer_phone' => '600 111 222',
            'appointment_date' => $startsAt->format('Y-m-d'),
            'appointment_time' => $startsAt->format('H:i'),
            'status' => 'confirmed',
        ];

        $prepared = $manager->prepare($form);
        $appointment = Appointment::query()->forceCreate($prepared);

        $this->assertEquals(45, $appointment->starts_at->diffInMinutes($appointment->ends_at));
        $this->assertArrayNotHasKey('appointment_date', $manager->prepare($form, $appointment));

        $this->expectException(ValidationException::class);
        $manager->prepare($form);
    }

    public function test_the_admin_grant_command_promotes_an_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('admin:grant', ['email' => 'ADMIN@example.com'])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }

    /** @return array{User, Service, Professional, CarbonImmutable} */
    private function catalog(): array
    {
        $user = User::factory()->create(['phone' => '600 111 222']);
        $service = Service::query()->create([
            'slug' => 'corte-admin',
            'name' => 'Corte de prueba',
            'duration_minutes' => 45,
            'is_active' => true,
        ]);
        $professional = Professional::query()->create([
            'slug' => 'profesional-admin',
            'name' => 'Laura Admin',
            'is_active' => true,
        ]);
        $professional->services()->attach($service);

        $startsAt = CarbonImmutable::now(config('app.business_timezone'))->next('Monday')->setTime(10, 0);
        Schedule::query()->create([
            'professional_id' => $professional->id,
            'day_of_week' => $startsAt->dayOfWeek,
            'starts_at' => '09:00',
            'ends_at' => '18:00',
            'slot_interval_minutes' => 30,
            'is_active' => true,
        ]);

        return [$user, $service, $professional, $startsAt];
    }
}
