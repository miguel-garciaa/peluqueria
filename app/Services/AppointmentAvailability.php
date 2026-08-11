<?php

namespace App\Services;

use App\Models\Professional;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AppointmentAvailability
{
    /**
     * @return array<int, array{time: string, period: string, professional: array{slug: string, name: string}}>
     */
    public function slots(CarbonImmutable $date, Service $service, ?Professional $professional = null): array
    {
        $dayStart = $date->startOfDay()->utc();
        $dayEnd = $date->endOfDay()->utc();

        $professionals = Professional::query()
            ->active()
            ->when($professional, fn (Builder $query) => $query->whereKey($professional->getKey()))
            ->whereHas('services', fn (Builder $query) => $query->whereKey($service->getKey())->where('services.is_active', true))
            ->with([
                'schedules' => fn ($query) => $query
                    ->where('day_of_week', $date->dayOfWeek)
                    ->where('is_active', true)
                    ->orderBy('starts_at'),
                'appointments' => fn ($query) => $query
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where('starts_at', '<', $dayEnd)
                    ->where('ends_at', '>', $dayStart),
            ])
            ->orderBy('id')
            ->get();

        $slots = [];
        foreach ($professionals as $candidate) {
            foreach ($candidate->schedules as $schedule) {
                $cursor = $date->setTimeFromTimeString(substr((string) $schedule->starts_at, 0, 5));
                $scheduleEnd = $date->setTimeFromTimeString(substr((string) $schedule->ends_at, 0, 5));

                while ($cursor->addMinutes($service->duration_minutes)->lessThanOrEqualTo($scheduleEnd)) {
                    $time = $cursor->format('H:i');
                    $endsAt = $cursor->addMinutes($service->duration_minutes);
                    $isFree = $cursor->isFuture() && ! $this->overlaps($candidate->appointments, $cursor->utc(), $endsAt->utc());

                    if ($isFree && ! isset($slots[$time])) {
                        $slots[$time] = [
                            'time' => $time,
                            'period' => $cursor->hour < 14 ? 'morning' : 'afternoon',
                            'professional' => ['slug' => $candidate->slug, 'name' => $candidate->name],
                        ];
                    }

                    $cursor = $cursor->addMinutes($schedule->slot_interval_minutes);
                }
            }
        }

        ksort($slots);

        return array_values($slots);
    }

    public function slotIsFree(Professional $professional, CarbonImmutable $startsAt, CarbonImmutable $endsAt): bool
    {
        $hasSchedule = $professional->schedules()
            ->where('day_of_week', $startsAt->dayOfWeek)
            ->where('is_active', true)
            ->get()
            ->contains(function ($schedule) use ($startsAt, $endsAt): bool {
                $scheduleStart = $startsAt->startOfDay()->setTimeFromTimeString(substr((string) $schedule->starts_at, 0, 5));
                $scheduleEnd = $startsAt->startOfDay()->setTimeFromTimeString(substr((string) $schedule->ends_at, 0, 5));

                if ($startsAt->lessThan($scheduleStart) || $endsAt->greaterThan($scheduleEnd)) {
                    return false;
                }

                return $scheduleStart->diffInMinutes($startsAt) % $schedule->slot_interval_minutes === 0;
            });

        if (! $hasSchedule || ! $startsAt->isFuture()) {
            return false;
        }

        return ! $professional->appointments()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('starts_at', '<', $endsAt->utc())
            ->where('ends_at', '>', $startsAt->utc())
            ->exists();
    }

    /** @param Collection<int, mixed> $appointments */
    private function overlaps(Collection $appointments, CarbonImmutable $startsAt, CarbonImmutable $endsAt): bool
    {
        return $appointments->contains(fn ($appointment) => $appointment->starts_at->lessThan($endsAt)
            && $appointment->ends_at->greaterThan($startsAt));
    }
}
