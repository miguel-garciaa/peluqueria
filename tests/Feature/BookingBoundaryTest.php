<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\ProfessionalCalendarEntry;
use App\Services\AppointmentAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesBookingData;
use Tests\TestCase;

class BookingBoundaryTest extends TestCase
{
    use CreatesBookingData;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[DataProvider('invalidPayloads')]
    public function test_booking_rejects_invalid_or_hostile_payloads(array $changes, array $errors): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [$user, $service, $professional] = $this->bookingCatalog();
        $this->schedule($professional);
        $payload = array_merge([
            'fullName' => 'Cliente Seguro',
            'phone' => '600123456',
            'serviceId' => $service->slug,
            'professionalId' => $professional->slug,
            'date' => '2026-09-14',
            'timeSlot' => '10:00',
            'paymentMethod' => 'cash',
        ], $changes);

        $this->actingAs($user)->postJson(route('bookings.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
        $this->assertDatabaseCount('bookings', 0);
    }

    public static function invalidPayloads(): array
    {
        return [
            'missing identity' => [['fullName' => '', 'phone' => ''], ['fullName', 'phone']],
            'one-letter name' => [['fullName' => 'A'], ['fullName']],
            'oversized name' => [['fullName' => str_repeat('a', 121)], ['fullName']],
            'foreign phone' => [['phone' => '+1 202 555 0100'], ['phone']],
            'invalid service' => [['serviceId' => '../../admin'], ['serviceId']],
            'invalid professional' => [['professionalId' => '<script>'], ['professionalId']],
            'past date' => [['date' => '2026-09-06'], ['date', 'timeSlot']],
            'outside booking horizon' => [['date' => '2026-12-07'], ['date']],
            'malformed date and time' => [['date' => '14/09/2026', 'timeSlot' => '10am'], ['date', 'timeSlot']],
            'unsupported payment method' => [['paymentMethod' => 'card'], ['paymentMethod']],
        ];
    }

    public function test_availability_query_trims_identifiers_and_validates_boundaries(): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [$user, $service, $professional] = $this->bookingCatalog();
        $this->schedule($professional);

        $this->actingAs($user)->getJson(route('bookings.availability', [
            'date' => '2026-09-14',
            'service' => '  '.$service->slug.'  ',
            'professional' => '  '.$professional->slug.'  ',
        ]))->assertOk()->assertJsonPath('slots.0.time', '09:00');

        foreach ([
            ['date' => '2026-09-06', 'service' => $service->slug, 'professional' => $professional->slug],
            ['date' => '2026-12-07', 'service' => $service->slug, 'professional' => $professional->slug],
            ['date' => '2026-09-14', 'service' => 'inactive', 'professional' => $professional->slug],
            ['date' => '2026-09-14', 'service' => $service->slug, 'professional' => 'missing'],
        ] as $query) {
            $this->actingAs($user)->getJson(route('bookings.availability', $query))->assertUnprocessable();
        }
    }

    public function test_slot_boundaries_allow_adjacent_appointments_but_reject_every_overlap_shape(): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [$user, $service, $professional] = $this->bookingCatalog();
        $this->schedule($professional);
        $day = CarbonImmutable::parse('2026-09-14', 'Europe/Madrid');
        $this->appointment($user, $service, $professional, $day->setTime(10, 0));
        $availability = app(AppointmentAvailability::class);

        $this->assertTrue($availability->slotIsFree($professional, $day->setTime(9, 0), $day->setTime(10, 0)));
        $this->assertTrue($availability->slotIsFree($professional, $day->setTime(11, 0), $day->setTime(12, 0)));
        $this->assertFalse($availability->slotIsFree($professional, $day->setTime(9, 30), $day->setTime(10, 30)));
        $this->assertFalse($availability->slotIsFree($professional, $day->setTime(10, 0), $day->setTime(11, 0)));
        $this->assertFalse($availability->slotIsFree($professional, $day->setTime(10, 30), $day->setTime(11, 30)));
    }

    public function test_cancelled_and_completed_records_do_not_block_a_slot(): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [$user, $service, $professional] = $this->bookingCatalog();
        $this->schedule($professional);
        $startsAt = CarbonImmutable::parse('2026-09-14 10:00', 'Europe/Madrid');

        foreach (['cancelled', 'completed'] as $status) {
            $appointment = $this->appointment($user, $service, $professional, $startsAt, $status);
            $this->assertTrue(app(AppointmentAvailability::class)->slotIsFree(
                $professional->fresh(),
                $startsAt,
                $startsAt->addHour(),
            ));
            $appointment->delete();
        }
    }

    public function test_partial_blocks_only_remove_slots_that_intersect_them(): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [, $service, $professional] = $this->bookingCatalog();
        $this->schedule($professional, startsAt: '09:00', endsAt: '13:00');
        ProfessionalCalendarEntry::query()->create([
            'professional_id' => $professional->id,
            'date' => '2026-09-14',
            'type' => 'blocked',
            'all_day' => false,
            'starts_at' => '10:30',
            'ends_at' => '11:15',
            'slot_interval_minutes' => 30,
        ]);

        $times = collect(app(AppointmentAvailability::class)->slots(
            CarbonImmutable::parse('2026-09-14', 'Europe/Madrid'),
            $service,
            $professional,
        ))->pluck('time')->all();

        $this->assertSame(['09:00', '09:30', '11:30', '12:00'], $times);
    }

    public function test_any_professional_uses_the_first_free_candidate_and_never_duplicates_times(): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [$user, $service, $first] = $this->bookingCatalog();
        $this->schedule($first);
        $second = Professional::query()->create([
            'slug' => 'second-professional', 'name' => 'Segunda profesional', 'is_active' => true,
        ]);
        $second->services()->attach($service);
        $this->schedule($second);
        $day = CarbonImmutable::parse('2026-09-14', 'Europe/Madrid');
        $this->appointment($user, $service, $first, $day->setTime(9, 0));

        $slots = app(AppointmentAvailability::class)->slots($day, $service);
        $nine = collect($slots)->firstWhere('time', '09:00');

        $this->assertSame($second->slug, $nine['professional']['slug']);
        $this->assertCount(count(array_unique(array_column($slots, 'time'))), $slots);
    }

    public function test_last_slot_must_fit_the_full_service_duration(): void
    {
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [, $service, $professional] = $this->bookingCatalog(['duration_minutes' => 75]);
        $this->schedule($professional, startsAt: '09:00', endsAt: '11:00', interval: 30);

        $times = collect(app(AppointmentAvailability::class)->slots(
            CarbonImmutable::parse('2026-09-14', 'Europe/Madrid'),
            $service,
            $professional,
        ))->pluck('time')->all();

        $this->assertSame(['09:00', '09:30'], $times);
    }

    public function test_successful_booking_ignores_privileged_fields_from_the_client(): void
    {
        Mail::fake();
        CarbonImmutable::setTestNow('2026-09-07 08:00:00 Europe/Madrid');
        [$user, $service, $professional] = $this->bookingCatalog();
        $this->schedule($professional);

        $this->actingAs($user)->postJson(route('bookings.store'), [
            'fullName' => "  Cliente\tSeguro  ",
            'phone' => '600123456',
            'serviceId' => $service->slug,
            'professionalId' => $professional->slug,
            'date' => '2026-09-14',
            'timeSlot' => '10:00',
            'paymentMethod' => 'cash',
            'status' => 'completed',
            'paymentAmount' => 0,
            'user_id' => 999999,
        ])->assertCreated();

        $appointment = Appointment::query()->sole();
        $this->assertSame($user->id, $appointment->user_id);
        $this->assertSame('confirmed', $appointment->status);
        $this->assertSame('35.00', $appointment->payment_amount);
        $this->assertSame('Cliente Seguro', $appointment->customer_name);
        Mail::assertQueued(AppointmentConfirmed::class, 1);
    }
}
