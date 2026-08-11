<?php

namespace Tests\Feature;

use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\ProfessionalCalendarEntry;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AppointmentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_booking_endpoints(): void
    {
        $this->postJson(route('bookings.store'))->assertUnauthorized();
        $this->getJson(route('bookings.availability'))->assertUnauthorized();
        $this->get(route('appointments.index'))->assertRedirect(route('login'));
        $this->patchJson(route('appointments.cancel', '01KTEST'))->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_book_and_the_email_is_queued(): void
    {
        Mail::fake();
        [$service, $professional] = $this->createCatalog();
        $user = User::factory()->create(['name' => 'María', 'phone' => null]);
        $date = CarbonImmutable::now('Europe/Madrid')->next('Monday')->format('Y-m-d');

        $this->actingAs($user)->postJson(route('bookings.store'), [
            'fullName' => 'María García',
            'phone' => '600 123 456',
            'serviceId' => $service->slug,
            'professionalId' => $professional->slug,
            'date' => $date,
            'timeSlot' => '10:30',
        ])->assertCreated()
            ->assertJsonPath('message', 'Cita confirmada. Recibirás los detalles por correo.')
            ->assertJsonPath('appointment.professional', $professional->name);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'status' => 'confirmed',
        ]);
        $this->assertSame('600 123 456', $user->fresh()->phone);
        Mail::assertQueued(AppointmentConfirmed::class, fn (AppointmentConfirmed $mail) => $mail->appointment->service_id === $service->id
            && $mail->connection === 'redis'
            && $mail->queue === 'emails');
    }

    public function test_availability_excludes_an_existing_appointment(): void
    {
        [$service, $professional] = $this->createCatalog();
        $user = User::factory()->create();
        $date = CarbonImmutable::now('Europe/Madrid')->next('Monday')->startOfDay();
        Appointment::query()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $user->name,
            'customer_phone' => '600 111 222',
            'starts_at' => $date->setTime(10, 30)->utc(),
            'ends_at' => $date->setTime(11, 30)->utc(),
            'status' => 'confirmed',
        ]);
        $loadedAppointment = $professional->appointments()->firstOrFail();
        $this->assertSame('10:30', $loadedAppointment->starts_at->timezone('Europe/Madrid')->format('H:i'));

        $response = $this->actingAs($user)->getJson(route('bookings.availability', [
            'date' => $date->format('Y-m-d'),
            'service' => $service->slug,
            'professional' => $professional->slug,
        ]))->assertOk();

        $times = collect($response->json('slots'))->pluck('time');
        $this->assertFalse($times->contains('10:00'), 'Slots devueltos: '.$times->join(', '));
        $this->assertFalse($times->contains('10:30'), 'Slots devueltos: '.$times->join(', '));
        $this->assertTrue($times->contains('11:30'));
    }

    public function test_an_all_day_calendar_block_removes_availability_and_prevents_booking(): void
    {
        Mail::fake();
        [$service, $professional] = $this->createCatalog();
        $user = User::factory()->create();
        $date = CarbonImmutable::now('Europe/Madrid')->next('Monday')->format('Y-m-d');

        ProfessionalCalendarEntry::query()->create([
            'professional_id' => $professional->id,
            'date' => $date,
            'type' => 'blocked',
            'all_day' => true,
            'reason' => 'Vacaciones',
        ]);

        $this->actingAs($user)->getJson(route('bookings.availability', [
            'date' => $date,
            'service' => $service->slug,
            'professional' => $professional->slug,
        ]))->assertOk()->assertJsonCount(0, 'slots');

        $this->actingAs($user)->postJson(route('bookings.store'), [
            'fullName' => $user->name,
            'phone' => '600 123 456',
            'serviceId' => $service->slug,
            'professionalId' => $professional->slug,
            'date' => $date,
            'timeSlot' => '10:30',
        ])->assertUnprocessable()->assertJsonValidationErrors('timeSlot');

        $this->assertDatabaseCount('bookings', 0);
        Mail::assertNothingQueued();
    }

    public function test_a_special_calendar_opening_replaces_the_weekly_schedule_for_that_date(): void
    {
        [$service, $professional] = $this->createCatalog();
        $user = User::factory()->create();
        $date = CarbonImmutable::now('Europe/Madrid')->next('Monday')->format('Y-m-d');

        ProfessionalCalendarEntry::query()->create([
            'professional_id' => $professional->id,
            'date' => $date,
            'type' => 'available',
            'all_day' => false,
            'starts_at' => '16:00',
            'ends_at' => '18:00',
            'slot_interval_minutes' => 30,
            'reason' => 'Apertura especial',
        ]);

        $response = $this->actingAs($user)->getJson(route('bookings.availability', [
            'date' => $date,
            'service' => $service->slug,
            'professional' => $professional->slug,
        ]))->assertOk();

        $this->assertSame(['16:00', '16:30', '17:00'], collect($response->json('slots'))->pluck('time')->all());
    }

    public function test_custom_service_details_are_required_and_limited_to_forty_words(): void
    {
        [$service, $professional] = $this->createCatalog(custom: true);
        $user = User::factory()->create();
        $date = CarbonImmutable::now('Europe/Madrid')->next('Monday')->format('Y-m-d');

        $this->actingAs($user)->postJson(route('bookings.store'), [
            'fullName' => 'María García',
            'phone' => '600 123 456',
            'serviceId' => $service->slug,
            'professionalId' => $professional->slug,
            'customDetails' => implode(' ', array_fill(0, 41, 'detalle')),
            'date' => $date,
            'timeSlot' => '10:30',
        ])->assertUnprocessable()->assertJsonValidationErrors('customDetails');
    }

    public function test_users_only_see_their_own_appointments(): void
    {
        [$service, $professional] = $this->createCatalog();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $date = CarbonImmutable::now('Europe/Madrid')->next('Monday')->setTime(10, 30);
        foreach ([$user, $otherUser] as $index => $owner) {
            Appointment::query()->create([
                'user_id' => $owner->id,
                'service_id' => $service->id,
                'professional_id' => $professional->id,
                'customer_name' => $owner->name,
                'customer_phone' => '600 111 222',
                'starts_at' => $date->addHours($index)->utc(),
                'ends_at' => $date->addHours($index + 1)->utc(),
                'status' => 'confirmed',
            ]);
        }

        $this->withoutVite();
        $response = $this->actingAs($user)->get(route('appointments.index'))->assertOk();
        $response->assertSee(Appointment::query()->where('user_id', $user->id)->value('reference'));
        $response->assertDontSee(Appointment::query()->where('user_id', $otherUser->id)->value('reference'));
        $response->assertSee('data-booking-endpoint="/reservas"', false);
        $response->assertSee('data-availability-endpoint="/reservas/disponibilidad"', false);
        $response->assertSee('data-booking-catalog=', false);
    }

    public function test_a_user_can_cancel_their_own_future_appointment(): void
    {
        Mail::fake();
        [$service, $professional] = $this->createCatalog();
        $user = User::factory()->create();
        $startsAt = CarbonImmutable::now('Europe/Madrid')->next('Monday')->setTime(10, 30);
        $appointment = Appointment::query()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $user->name,
            'customer_phone' => '600 111 222',
            'starts_at' => $startsAt->utc(),
            'ends_at' => $startsAt->addHour()->utc(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($user)
            ->patch(route('appointments.cancel', $appointment->reference))
            ->assertRedirect(route('appointments.index'))
            ->assertSessionHas('appointment_status', 'La cita se ha anulado correctamente. Recibirás la confirmación por correo.');

        $this->assertDatabaseHas('bookings', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
        $this->assertNotNull($appointment->fresh()->cancelled_at);

        Mail::assertQueued(AppointmentCancelled::class, fn (AppointmentCancelled $mail) => $mail->appointment->is($appointment)
            && $mail->connection === 'redis'
            && $mail->queue === 'emails');

        $this->withoutVite();
        $this->actingAs($user)
            ->get(route('appointments.index'))
            ->assertOk()
            ->assertDontSee($appointment->reference);

        $availability = $this->actingAs($user)->getJson(route('bookings.availability', [
            'date' => $startsAt->format('Y-m-d'),
            'service' => $service->slug,
            'professional' => $professional->slug,
        ]))->assertOk();
        $this->assertContains('10:30', collect($availability->json('slots'))->pluck('time')->all());
    }

    public function test_the_cancellation_email_contains_the_cancelled_appointment_details(): void
    {
        [$service, $professional] = $this->createCatalog();
        $user = User::factory()->create(['name' => 'María']);
        $startsAt = CarbonImmutable::now('Europe/Madrid')->next('Monday')->setTime(10, 30);
        $appointment = Appointment::query()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $user->name,
            'customer_phone' => '600 111 222',
            'starts_at' => $startsAt->utc(),
            'ends_at' => $startsAt->addHour()->utc(),
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
        $appointment->load(['service', 'professional', 'user']);

        $html = (new AppointmentCancelled($appointment))->render();

        $this->assertStringContainsString('Tu cita ha sido anulada', $html);
        $this->assertStringContainsString(e($service->name), $html);
        $this->assertStringContainsString($professional->name, $html);
        $this->assertStringContainsString($appointment->reference, $html);

        foreach ([$html, (new AppointmentConfirmed($appointment))->render()] as $mailHtml) {
            $this->assertStringNotContainsString('border-radius:20px 20px 0 0', $mailHtml);
            $this->assertStringNotContainsString('border-radius:0 0 20px 20px', $mailHtml);
        }
    }

    public function test_a_user_cannot_cancel_another_users_or_a_past_appointment(): void
    {
        [$service, $professional] = $this->createCatalog();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $appointment = Appointment::query()->create([
            'user_id' => $owner->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $owner->name,
            'customer_phone' => '600 111 222',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subDay()->addHour(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($otherUser)
            ->patch(route('appointments.cancel', $appointment->reference))
            ->assertNotFound();

        $this->actingAs($owner)
            ->patch(route('appointments.cancel', $appointment->reference))
            ->assertRedirect(route('appointments.index'))
            ->assertSessionHas('appointment_error');

        $this->assertSame('confirmed', $appointment->fresh()->status);
        $this->assertNull($appointment->fresh()->cancelled_at);
    }

    /** @return array{Service, Professional} */
    private function createCatalog(bool $custom = false): array
    {
        $service = Service::query()->create([
            'slug' => $custom ? 'custom' : 'cut',
            'name' => $custom ? 'Personalizado' : 'Corte & Peinado',
            'duration_minutes' => 60,
            'price_from' => $custom ? null : 35,
            'is_custom' => $custom,
            'is_active' => true,
        ]);
        $professional = Professional::query()->create([
            'slug' => 'marta',
            'name' => 'Marta Soler',
            'role' => 'Especialista en color',
            'is_active' => true,
        ]);
        $professional->services()->attach($service);
        Schedule::query()->create([
            'professional_id' => $professional->id,
            'day_of_week' => 1,
            'starts_at' => '09:30',
            'ends_at' => '20:00',
            'slot_interval_minutes' => 30,
            'is_active' => true,
        ]);

        return [$service, $professional];
    }
}
