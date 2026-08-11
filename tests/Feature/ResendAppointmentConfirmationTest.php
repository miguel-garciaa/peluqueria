<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResendAppointmentConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_existing_confirmation_can_be_queued_again(): void
    {
        Mail::fake();
        $this->seed();
        $user = User::factory()->create();
        $appointment = Appointment::query()->create([
            'user_id' => $user->id,
            'service_id' => Service::query()->firstOrFail()->id,
            'professional_id' => Professional::query()->firstOrFail()->id,
            'customer_name' => $user->name,
            'customer_phone' => '600 123 456',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'confirmed',
        ]);

        $this->artisan('appointments:resend-confirmation', ['reference' => $appointment->reference])
            ->assertSuccessful();

        Mail::assertQueued(AppointmentConfirmed::class, fn (AppointmentConfirmed $mail) => $mail->appointment->is($appointment)
            && $mail->connection === 'redis'
            && $mail->queue === 'emails');
    }
}
