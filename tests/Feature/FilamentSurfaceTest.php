<?php

namespace Tests\Feature;

use App\Filament\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\Pages\ViewAppointment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ViewPayment;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\ProfessionalCalendarEntries\Pages\EditProfessionalCalendarEntry;
use App\Filament\Resources\ProfessionalCalendarEntries\Pages\ViewProfessionalCalendarEntry;
use App\Filament\Resources\Professionals\Pages\EditProfessional;
use App\Filament\Resources\Professionals\Pages\ViewProfessional;
use App\Filament\Resources\Schedules\Pages\ViewSchedule;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ViewService;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Widgets\UpcomingBookings;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProfessionalCalendarEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesBookingData;
use Tests\TestCase;

class FilamentSurfaceTest extends TestCase
{
    use CreatesBookingData;
    use RefreshDatabase;

    public function test_every_resource_detail_schema_renders_for_an_administrator(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$customer, $service, $professional] = $this->bookingCatalog();
        $schedule = $this->schedule($professional);
        $appointment = $this->appointment(
            $customer,
            $service,
            $professional,
            CarbonImmutable::now()->addDay()->setTime(10, 0),
        );
        $payment = Payment::query()->create([
            'booking_id' => $appointment->id,
            'method' => 'cash',
            'status' => 'paid',
            'amount' => 35,
            'paid_at' => now(),
        ]);
        $product = Product::query()->create([
            'name' => 'Producto de prueba',
            'category' => 'hair_care',
            'price' => 10,
            'units' => 2,
            'low_stock_threshold' => 3,
            'is_active' => true,
        ]);
        $calendarEntry = ProfessionalCalendarEntry::query()->create([
            'professional_id' => $professional->id,
            'date' => CarbonImmutable::now()->addDay()->toDateString(),
            'type' => 'blocked',
            'all_day' => true,
            'reason' => 'Descanso',
        ]);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        foreach ([
            [ViewAppointment::class, $appointment],
            [ViewPayment::class, $payment],
            [ViewProduct::class, $product],
            [ViewProfessionalCalendarEntry::class, $calendarEntry],
            [ViewProfessional::class, $professional],
            [ViewSchedule::class, $schedule],
            [ViewService::class, $service],
            [ViewUser::class, $customer],
        ] as [$component, $record]) {
            Livewire::test($component, ['record' => $record->getRouteKey()])->assertSuccessful();
        }
    }

    public function test_uncovered_create_edit_forms_and_dashboard_widget_render(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$customer, $service, $professional] = $this->bookingCatalog();
        $appointment = $this->appointment(
            $customer,
            $service,
            $professional,
            CarbonImmutable::now()->addDay()->setTime(10, 0),
        );
        $payment = Payment::query()->create([
            'booking_id' => $appointment->id,
            'method' => 'cash',
            'status' => 'paid',
            'amount' => 35,
            'paid_at' => now(),
        ]);
        $calendarEntry = ProfessionalCalendarEntry::query()->create([
            'professional_id' => $professional->id,
            'date' => CarbonImmutable::now()->addDay()->toDateString(),
            'type' => 'available',
            'all_day' => false,
            'starts_at' => '16:00',
            'ends_at' => '18:00',
            'slot_interval_minutes' => 30,
        ]);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateAppointment::class)->assertSuccessful();
        foreach ([
            [EditAppointment::class, $appointment],
            [EditPayment::class, $payment],
            [EditProfessionalCalendarEntry::class, $calendarEntry],
            [EditProfessional::class, $professional],
            [EditService::class, $service],
            [EditUser::class, $customer],
        ] as [$component, $record]) {
            Livewire::test($component, ['record' => $record->getRouteKey()])->assertSuccessful();
        }

        Livewire::test(UpcomingBookings::class)->assertSuccessful();
    }
}
