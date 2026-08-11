<?php

namespace App\Services;

use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BookAppointment
{
    public function __construct(private readonly AppointmentAvailability $availability) {}

    /** @param array{fullName: string, phone: string, serviceId: string, professionalId: string, customDetails?: string|null, date: string, timeSlot: string} $data */
    public function handle(User $user, array $data): Appointment
    {
        $service = Service::query()->active()->where('slug', $data['serviceId'])->firstOrFail();
        $startsAt = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $data['date'].' '.$data['timeSlot'],
            config('app.business_timezone'),
        );
        $endsAt = $startsAt->addMinutes($service->duration_minutes);

        $appointment = DB::transaction(function () use ($data, $endsAt, $service, $startsAt, $user): Appointment {
            $professionals = Professional::query()
                ->active()
                ->when($data['professionalId'] !== 'any', fn (Builder $query) => $query->where('slug', $data['professionalId']))
                ->whereHas('services', fn (Builder $query) => $query->whereKey($service->getKey()))
                ->with([
                    'schedules' => fn ($query) => $query
                        ->select(['id', 'professional_id', 'starts_at', 'ends_at', 'slot_interval_minutes'])
                        ->where('day_of_week', $startsAt->dayOfWeek)
                        ->where('is_active', true)
                        ->orderBy('starts_at'),
                    'calendarEntries' => fn ($query) => $query
                        ->select(['id', 'professional_id', 'type', 'all_day', 'starts_at', 'ends_at', 'slot_interval_minutes'])
                        ->whereDate('date', $startsAt->format('Y-m-d'))
                        ->orderBy('starts_at'),
                ])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $professional = $professionals->first(
                fn (Professional $candidate) => $this->availability->slotIsFree($candidate, $startsAt, $endsAt),
            );

            if (! $professional) {
                throw ValidationException::withMessages([
                    'timeSlot' => 'Esa hora acaba de dejar de estar disponible. Elige otra.',
                ]);
            }

            $user->forceFill([
                'name' => $data['fullName'],
                'phone' => $data['phone'],
            ])->save();

            return Appointment::query()->create([
                'user_id' => $user->getKey(),
                'service_id' => $service->getKey(),
                'professional_id' => $professional->getKey(),
                'customer_name' => $data['fullName'],
                'customer_phone' => $data['phone'],
                'custom_details' => $service->is_custom ? trim((string) ($data['customDetails'] ?? '')) : null,
                'starts_at' => $startsAt->utc(),
                'ends_at' => $endsAt->utc(),
                'status' => 'confirmed',
            ]);
        }, 3);

        $appointment->load(['service', 'professional', 'user']);

        Mail::to($user->email)->queue(
            (new AppointmentConfirmed($appointment))
                ->onConnection('redis')
                ->onQueue('emails')
                ->afterCommit(),
        );

        return $appointment;
    }
}
