<?php

namespace App\Services;

use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BookAppointment
{
    public function __construct(
        private readonly AppointmentAvailability $availability,
        private readonly CacheManager $cache,
    ) {}

    /** @param array{fullName: string, phone: string, serviceId: string, professionalId: string, customDetails?: string|null, date: string, timeSlot: string} $data */
    public function handle(User $user, array $data): Appointment
    {
        $startsAt = CarbonImmutable::createFromFormat(
            '!Y-m-d H:i',
            $data['date'].' '.$data['timeSlot'],
            config('app.business_timezone'),
        );

        // The day-wide mutex also serializes overlapping services that start at different times.
        $lockKey = 'bookings:create:'.hash('sha256', $startsAt->format('Y-m-d'));
        $lock = $this->cache
            ->store(config('cache.booking_lock_store'))
            ->lock($lockKey, 30);

        try {
            /** @var Appointment $appointment */
            $appointment = $lock->block(
                3,
                fn (): Appointment => $this->createBooking($user, $data, $startsAt),
            );
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages([
                'timeSlot' => 'Otra reserva está procesando ese horario. Inténtalo de nuevo en unos segundos.',
            ]);
        }

        $appointment->load(['service', 'professional', 'user']);

        Mail::to($user->email)->queue(
            (new AppointmentConfirmed($appointment))
                ->onConnection('redis')
                ->onQueue('emails')
                ->afterCommit(),
        );

        return $appointment;
    }

    /** @param array<string, mixed> $data */
    private function createBooking(User $user, array $data, CarbonImmutable $startsAt): Appointment
    {
        return DB::transaction(function () use ($user, $data, $startsAt): Appointment {
            $service = Service::query()
                ->active()
                ->where('slug', $data['serviceId'])
                ->lockForUpdate()
                ->first();

            if (! $service) {
                throw ValidationException::withMessages([
                    'serviceId' => 'El servicio seleccionado ya no está disponible.',
                ]);
            }

            $endsAt = $startsAt->addMinutes($service->duration_minutes);

            $professionals = Professional::query()
                ->active()
                ->when(
                    $data['professionalId'] !== 'any',
                    fn (Builder $query) => $query->where('slug', $data['professionalId']),
                )
                ->whereHas(
                    'services',
                    fn (Builder $query) => $query->whereKey($service->getKey()),
                )
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
                fn (Professional $candidate): bool => $this->availability->slotIsFree(
                    $candidate,
                    $startsAt,
                    $endsAt,
                ),
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

            $appointment = new Appointment;
            $appointment->fill([
                'customer_name' => $data['fullName'],
                'customer_phone' => $data['phone'],
                'custom_details' => $service->is_custom ? ($data['customDetails'] ?? null) : null,
            ]);
            $appointment->user()->associate($user);
            $appointment->service()->associate($service);
            $appointment->professional()->associate($professional);
            $appointment->starts_at = $startsAt->utc();
            $appointment->ends_at = $endsAt->utc();
            $appointment->status = 'confirmed';
            $appointment->save();

            return $appointment;
        }, attempts: 3);
    }
}
