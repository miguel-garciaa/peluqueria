<?php

namespace Tests\Feature;

use App\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_appointment_request_is_stored(): void
    {
        $date = CarbonImmutable::now('Europe/Madrid')->next('Monday')->format('Y-m-d');

        $this->postJson(route('bookings.store'), [
            'fullName' => 'María García',
            'phone' => '600 123 456',
            'serviceId' => 'balayage',
            'professionalId' => 'marta',
            'date' => $date,
            'timeSlot' => '10:30',
        ])->assertCreated()
            ->assertJsonPath('message', 'Solicitud recibida. Te contactaremos muy pronto.');

        $this->assertDatabaseHas('appointment_requests', [
            'full_name' => 'María García',
            'phone' => '600 123 456',
            'service_id' => 'balayage',
            'professional_id' => 'marta',
            'time_slot' => '10:30',
            'status' => 'pending',
        ]);

        $appointmentRequest = AppointmentRequest::query()->firstOrFail();
        $this->assertSame($date, $appointmentRequest->requested_date->toDateString());
    }

    public function test_sundays_are_rejected(): void
    {
        $date = CarbonImmutable::now('Europe/Madrid')->next('Sunday')->format('Y-m-d');

        $this->postJson(route('bookings.store'), [
            'fullName' => 'María García',
            'phone' => '600 123 456',
            'serviceId' => 'cut',
            'professionalId' => 'any',
            'date' => $date,
            'timeSlot' => '10:30',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    }
}
