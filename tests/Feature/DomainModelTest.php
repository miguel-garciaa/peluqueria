<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\User;
use App\Support\AppointmentHistoryPeriod;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesBookingData;
use Tests\TestCase;

class DomainModelTest extends TestCase
{
    use CreatesBookingData;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[DataProvider('cancellationStates')]
    public function test_only_future_active_appointments_can_be_cancelled(string $status, int $minutesFromNow, bool $expected): void
    {
        CarbonImmutable::setTestNow('2026-09-14 08:00:00 UTC');
        [$user, $service, $professional] = $this->bookingCatalog();
        $appointment = $this->appointment(
            $user,
            $service,
            $professional,
            CarbonImmutable::now()->addMinutes($minutesFromNow),
            $status,
        );

        $this->assertSame($expected, $appointment->canBeCancelled());
    }

    public static function cancellationStates(): array
    {
        return [
            'future confirmed' => ['confirmed', 60, true],
            'future pending' => ['pending', 60, true],
            'past confirmed' => ['confirmed', -60, false],
            'future cancelled' => ['cancelled', 60, false],
            'future completed' => ['completed', 60, false],
        ];
    }

    public function test_appointments_generate_a_unique_ulid_and_normalize_phone_numbers(): void
    {
        [$user, $service, $professional] = $this->bookingCatalog();
        $first = $this->appointment($user, $service, $professional, CarbonImmutable::now()->addDay(), overrides: [
            'customer_phone' => '0034 612-345-678',
        ]);
        $second = $this->appointment($user, $service, $professional, CarbonImmutable::now()->addDays(2));

        $this->assertTrue(Str::isUlid($first->reference));
        $this->assertNotSame($first->reference, $second->reference);
        $this->assertSame('+34 612 34 56 78', $first->customer_phone);
        $this->assertSame('+34 612 34 56 78', $first->getRawOriginal('customer_phone'));
    }

    public function test_user_phone_is_normalized_on_write_and_read(): void
    {
        $user = User::factory()->create(['phone' => '+34699123456']);

        $this->assertSame('+34 699 12 34 56', $user->phone);
        $this->assertSame('+34 699 12 34 56', $user->getRawOriginal('phone'));
    }

    public function test_configured_admin_email_is_case_insensitive_and_excluded_from_customers(): void
    {
        config(['admin.email' => 'Owner@Example.com']);
        $configured = User::factory()->create(['email' => 'owner@example.com', 'is_admin' => false]);
        $flagged = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->assertTrue($configured->isPanelAdmin());
        $this->assertTrue($flagged->isPanelAdmin());
        $this->assertFalse($customer->isPanelAdmin());
        $this->assertEqualsCanonicalizing([$configured->id, $flagged->id], User::query()->admins()->pluck('id')->all());
        $this->assertSame([$customer->id], User::query()->customers()->pluck('id')->all());
        $this->assertTrue($configured->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_no_configured_admin_email_keeps_scopes_consistent(): void
    {
        config(['admin.email' => '  ']);
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->assertSame([$admin->id], User::query()->admins()->pluck('id')->all());
        $this->assertSame([$customer->id], User::query()->customers()->pluck('id')->all());
    }

    #[DataProvider('scheduleLabels')]
    public function test_schedule_groups_have_human_readable_labels(array $days, string $expected): void
    {
        [, , $professional] = $this->bookingCatalog();
        $group = (string) Str::ulid();
        $records = collect($days)->map(fn (int $day) => Schedule::query()->create([
            'group_id' => $group,
            'professional_id' => $professional->id,
            'day_of_week' => $day,
            'starts_at' => '09:00',
            'ends_at' => '14:00',
            'slot_interval_minutes' => 30,
            'is_active' => true,
        ]));

        $record = $records->first()->load('groupSchedules');
        $this->assertSame($days, $record->groupedDays());
        $this->assertSame($expected, $record->days_label);
    }

    public static function scheduleLabels(): array
    {
        return [
            'weekdays' => [[1, 2, 3, 4, 5], 'Lunes–Viernes'],
            'monday through saturday' => [[1, 2, 3, 4, 5, 6], 'Lunes–Sábado'],
            'whole week' => [[1, 2, 3, 4, 5, 6, 0], 'Todos los días'],
            'custom order' => [[2, 4, 0], 'Mar, Jue, Dom'],
        ];
    }

    public function test_ungrouped_schedule_reports_its_single_day(): void
    {
        [, , $professional] = $this->bookingCatalog();
        $schedule = $this->schedule($professional, dayOfWeek: 3);
        $schedule->forceFill(['group_id' => null]);

        $this->assertSame([3], $schedule->groupedDays());
        $this->assertSame('Mié', $schedule->days_label);
    }

    public function test_product_category_fallback_scopes_and_casts_are_stable(): void
    {
        Product::query()->create([
            'name' => 'Champú', 'category' => 'hair_care', 'price' => 12.5,
            'units' => 2, 'low_stock_threshold' => 3, 'is_active' => true,
        ]);
        $unknown = Product::query()->create([
            'name' => 'Otro', 'category' => 'unexpected', 'price' => 9,
            'units' => 8, 'low_stock_threshold' => 1, 'is_active' => false,
        ]);

        $this->assertSame('Otros', $unknown->categoryLabel());
        $this->assertSame('12.50', Product::query()->active()->sole()->price);
        $this->assertSame('Champú', Product::query()->lowStock()->sole()->name);
        $this->assertArrayHasKey('machinery', Product::categoryOptions());
    }

    public function test_payment_options_relations_and_json_casts_are_stable(): void
    {
        [$user, $service, $professional] = $this->bookingCatalog();
        $appointment = $this->appointment($user, $service, $professional, CarbonImmutable::now()->subDay(), 'completed');
        $payment = Payment::query()->create([
            'booking_id' => $appointment->id,
            'method' => 'bizum',
            'status' => 'paid',
            'amount' => 35,
            'paid_at' => CarbonImmutable::now(),
            'metadata' => ['order' => 'A-1'],
        ]);

        $this->assertTrue($payment->appointment->is($appointment));
        $this->assertSame(['order' => 'A-1'], $payment->metadata);
        $this->assertSame('35.00', $payment->amount);
        $this->assertSame('Bizum', Payment::methodOptions()['bizum']);
        $this->assertSame('Reembolsado', Payment::statusOptions()['refunded']);
    }

    public function test_history_period_contract_covers_every_enum_case(): void
    {
        $now = CarbonImmutable::parse('2026-09-10 12:00:00', 'Europe/Madrid');

        $this->assertSame(AppointmentHistoryPeriod::Month, AppointmentHistoryPeriod::fromValue('invalid'));
        $this->assertSame([
            'day' => 'Últimas 24 horas',
            'week' => 'Últimos 7 días',
            'month' => 'Últimos 30 días',
            'quarter' => 'Últimos 3 meses',
            'all' => 'Histórico completo',
        ], AppointmentHistoryPeriod::options());

        foreach (AppointmentHistoryPeriod::cases() as $period) {
            $this->assertNotSame('', $period->label());
            $this->assertNotSame('', $period->fileLabel());
            $this->assertStringContainsString('10/09/2026 12:00', $period->rangeLabel($now));
        }

        $this->assertNull(AppointmentHistoryPeriod::All->startsAt($now));
        $this->assertTrue(AppointmentHistoryPeriod::Quarter->startsAt($now)->equalTo($now->subMonths(3)));
    }
}
