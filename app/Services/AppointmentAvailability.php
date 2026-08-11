<?php

namespace App\Services;

use App\Models\Appointment;
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
                'calendarEntries' => fn ($query) => $query
                    ->whereDate('date', $date->format('Y-m-d'))
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
            foreach ($this->effectiveIntervals($candidate) as $interval) {
                $cursor = $date->setTimeFromTimeString($interval['starts_at']);
                $scheduleEnd = $date->setTimeFromTimeString($interval['ends_at']);

                while ($cursor->addMinutes($service->duration_minutes)->lessThanOrEqualTo($scheduleEnd)) {
                    $time = $cursor->format('H:i');
                    $endsAt = $cursor->addMinutes($service->duration_minutes);
                    $isFree = $cursor->isFuture()
                        && ! $this->isBlocked($candidate, $cursor, $endsAt)
                        && ! $this->overlaps($candidate->appointments, $cursor->utc(), $endsAt->utc());

                    if ($isFree && ! isset($slots[$time])) {
                        $slots[$time] = [
                            'time' => $time,
                            'period' => $cursor->hour < 14 ? 'morning' : 'afternoon',
                            'professional' => ['slug' => $candidate->slug, 'name' => $candidate->name],
                        ];
                    }

                    $cursor = $cursor->addMinutes($interval['slot_interval_minutes']);
                }
            }
        }

        ksort($slots);

        return array_values($slots);
    }

    public function slotIsFree(
        Professional $professional,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        ?Appointment $except = null,
    ): bool {
        $professional->load([
            'schedules' => fn ($query) => $query
                ->where('day_of_week', $startsAt->dayOfWeek)
                ->where('is_active', true)
                ->orderBy('starts_at'),
            'calendarEntries' => fn ($query) => $query
                ->whereDate('date', $startsAt->format('Y-m-d'))
                ->orderBy('starts_at'),
        ]);

        $hasSchedule = collect($this->effectiveIntervals($professional))
            ->contains(function (array $interval) use ($startsAt, $endsAt): bool {
                $scheduleStart = $startsAt->startOfDay()->setTimeFromTimeString($interval['starts_at']);
                $scheduleEnd = $startsAt->startOfDay()->setTimeFromTimeString($interval['ends_at']);

                if ($startsAt->lessThan($scheduleStart) || $endsAt->greaterThan($scheduleEnd)) {
                    return false;
                }

                return $scheduleStart->diffInMinutes($startsAt) % $interval['slot_interval_minutes'] === 0;
            });

        if (! $hasSchedule || ! $startsAt->isFuture() || $this->isBlocked($professional, $startsAt, $endsAt)) {
            return false;
        }

        return ! $professional->appointments()
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($except, fn (Builder $query) => $query->whereKeyNot($except->getKey()))
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

    /**
     * @return array<int, array{starts_at: string, ends_at: string, slot_interval_minutes: int}>
     */
    private function effectiveIntervals(Professional $professional): array
    {
        $availableOverrides = $professional->calendarEntries
            ->where('type', 'available')
            ->where('all_day', false)
            ->filter(fn ($entry) => $entry->starts_at && $entry->ends_at);

        $source = $availableOverrides->isNotEmpty() ? $availableOverrides : $professional->schedules;

        return $source->map(fn ($interval) => [
            'starts_at' => substr((string) $interval->starts_at, 0, 5),
            'ends_at' => substr((string) $interval->ends_at, 0, 5),
            'slot_interval_minutes' => (int) $interval->slot_interval_minutes,
        ])->values()->all();
    }

    private function isBlocked(Professional $professional, CarbonImmutable $startsAt, CarbonImmutable $endsAt): bool
    {
        return $professional->calendarEntries
            ->where('type', 'blocked')
            ->contains(function ($entry) use ($startsAt, $endsAt): bool {
                if ($entry->all_day) {
                    return true;
                }

                if (! $entry->starts_at || ! $entry->ends_at) {
                    return false;
                }

                $blockedFrom = $startsAt->startOfDay()->setTimeFromTimeString(substr((string) $entry->starts_at, 0, 5));
                $blockedUntil = $startsAt->startOfDay()->setTimeFromTimeString(substr((string) $entry->ends_at, 0, 5));

                return $startsAt->lessThan($blockedUntil) && $endsAt->greaterThan($blockedFrom);
            });
    }
}
