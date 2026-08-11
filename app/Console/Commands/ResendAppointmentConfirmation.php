<?php

namespace App\Console\Commands;

use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ResendAppointmentConfirmation extends Command
{
    protected $signature = 'appointments:resend-confirmation {reference : Referencia ULID de la cita}';

    protected $description = 'Vuelve a poner en cola el correo de confirmación de una cita';

    public function handle(): int
    {
        $appointment = Appointment::query()
            ->with(['service', 'professional', 'user'])
            ->where('reference', $this->argument('reference'))
            ->first();

        if (! $appointment) {
            $this->error('No existe una cita con esa referencia.');

            return self::FAILURE;
        }

        Mail::to($appointment->user->email)->queue(
            (new AppointmentConfirmed($appointment))
                ->onConnection('redis')
                ->onQueue('emails'),
        );

        $this->info("Confirmación encolada para {$appointment->user->email}.");

        return self::SUCCESS;
    }
}
