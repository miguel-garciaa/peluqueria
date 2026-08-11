<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Professional;
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
            ->assertJsonPath('message', 'Cita confirmada. Te hemos enviado los detalles por correo.')
            ->assertJsonPath('appointment.professional', $professional->name);

        $this->assertDatabaseHas('appointments', [
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
