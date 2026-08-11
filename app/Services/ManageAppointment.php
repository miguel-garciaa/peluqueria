<?php

namespace App\Services;

use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ManageAppointment
{
    public function __construct(private readonly AppointmentAvailability $availability) {}

    /** @param array<string, mixed> $data */
    public function prepare(array $data, ?Appointment $except = null): array
    {
        $service = Service::query()->findOrFail($data['service_id']);
        $professional = Professional::query()->lockForUpdate()->findOrFail($data['professional_id']);
        $status = (string) ($data['status'] ?? 'confirmed');

        $worksWithService = $professional->services()->whereKey($service->getKey())->exists();
        if (! $worksWithService) {
            throw ValidationException::withMessages([
                'data.professional_id' => 'Este profesional no realiza el servicio seleccionado.',
            ]);
        }

        $startsAt = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $data['appointment_date'].' '.$data['appointment_time'],
            config('app.business_timezone'),
        );
        $endsAt = $startsAt->addMinutes($service->duration_minutes);

        if (in_array($status, ['pending', 'confirmed'], true)
            && ! $this->availability->slotIsFree($professional, $startsAt, $endsAt, $except)) {
            throw ValidationException::withMessages([
                'data.appointment_time' => 'Ese horario no está disponible para el profesional seleccionado.',
            ]);
        }

        unset($data['appointment_date'], $data['appointment_time']);

        $data['starts_at'] = $startsAt->utc();
        $data['ends_at'] = $endsAt->utc();
        $data['cancelled_at'] = $status === 'cancelled'
            ? ($except?->cancelled_at ?? now())
            : null;

        return $data;
    }

    public function sendConfirmation(Appointment $appointment): void
    {
        $appointment->loadMissing(['service', 'professional', 'user']);
        Mail::to($appointment->user->email)->queue(
            (new AppointmentConfirmed($appointment))
                ->onConnection('redis')
                ->onQueue('emails')
                ->afterCommit(),
        );
    }

    public function sendCancellation(Appointment $appointment): void
    {
        $appointment->loadMissing(['service', 'professional', 'user']);
        Mail::to($appointment->user->email)->queue(
            (new AppointmentCancelled($appointment))
                ->onConnection('redis')
                ->onQueue('emails')
                ->afterCommit(),
        );
    }

    public function cancel(Appointment $appointment): bool
    {
        $cancelled = DB::transaction(function () use ($appointment): ?Appointment {
            $lockedAppointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->getKey());

            if (! $lockedAppointment->canBeCancelled()) {
                return null;
            }

            $lockedAppointment->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $lockedAppointment;
        }, 3);

        if (! $cancelled) {
            return false;
        }

        $appointment->refresh();
        $this->sendCancellation($cancelled);

        return true;
    }
}
