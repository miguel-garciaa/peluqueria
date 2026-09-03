<?php

namespace App\Services;

use App\Models\Professional;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManageScheduleGroup
{
    /** @param array<string, mixed> $data */
    public function create(array $data): Schedule
    {
        [$attributes, $days] = $this->normalize($data);

        return DB::transaction(function () use ($attributes, $days): Schedule {
            Professional::query()->lockForUpdate()->findOrFail($attributes['professional_id']);
            $this->ensureDaysAreFree($attributes, $days);

            return $this->insertGroup((string) Str::ulid(), $attributes, $days);
        }, 3);
    }

    /** @param array<string, mixed> $data */
    public function update(Schedule $record, array $data): Schedule
    {
        [$attributes, $days] = $this->normalize($data);

        return DB::transaction(function () use ($record, $attributes, $days): Schedule {
            Professional::query()->lockForUpdate()->findOrFail($attributes['professional_id']);
            $groupId = filled($record->group_id) ? (string) $record->group_id : (string) Str::ulid();

            if (blank($record->group_id)) {
                $record->forceFill(['group_id' => $groupId])->save();
            }

            /** @var Collection<int, Schedule> $currentGroup */
            $currentGroup = Schedule::query()
                ->where('group_id', $groupId)
                ->lockForUpdate()
                ->orderBy('id')
                ->get();

            abort_if($currentGroup->isEmpty(), 404);
            $this->ensureDaysAreFree($attributes, $days, $groupId);

            $keeper = $currentGroup->firstWhere('id', $record->id) ?? $currentGroup->firstOrFail();
            Schedule::query()
                ->where('group_id', $record->group_id)
                ->whereKeyNot($keeper->getKey())
                ->delete();

            $keeper->update([
                ...$attributes,
                'day_of_week' => array_shift($days),
            ]);

            foreach ($days as $day) {
                Schedule::query()->create([
                    ...$attributes,
                    'group_id' => $keeper->group_id,
                    'day_of_week' => $day,
                ]);
            }

            return $keeper->refresh()->load('groupSchedules');
        }, 3);
    }

    public function delete(Schedule $record): bool
    {
        if (blank($record->group_id)) {
            return (bool) $record->delete();
        }

        return DB::transaction(fn (): bool => Schedule::query()
            ->where('group_id', $record->group_id)
            ->delete() > 0, 3);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{array<string, mixed>, array<int, int>}
     */
    private function normalize(array $data): array
    {
        $days = collect($data['days_of_week'] ?? [])
            ->map(fn (mixed $day): int => (int) $day)
            ->filter(fn (int $day): bool => array_key_exists($day, Schedule::DAY_LABELS))
            ->unique()
            ->sortBy(fn (int $day): int => $day === 0 ? 7 : $day)
            ->values()
            ->all();

        if ($days === []) {
            throw ValidationException::withMessages([
                'data.days_of_week' => 'Selecciona al menos un día de trabajo.',
            ]);
        }

        unset($data['days_of_week']);

        return [$data, $days];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int>  $days
     */
    private function ensureDaysAreFree(array $attributes, array $days, ?string $exceptGroup = null): void
    {
        $conflictingDays = Schedule::query()
            ->where('professional_id', $attributes['professional_id'])
            ->whereIn('day_of_week', $days)
            ->when($exceptGroup, fn ($query) => $query->where('group_id', '!=', $exceptGroup))
            ->where('starts_at', '<', $attributes['ends_at'])
            ->where('ends_at', '>', $attributes['starts_at'])
            ->pluck('day_of_week')
            ->map(fn (mixed $day): int => (int) $day)
            ->unique()
            ->sortBy(fn (int $day): int => $day === 0 ? 7 : $day);

        if ($conflictingDays->isEmpty()) {
            return;
        }

        throw ValidationException::withMessages([
            'data.days_of_week' => 'Ya existe un horario que se solapa en: '.$conflictingDays
                ->map(fn (int $day): string => Schedule::DAY_LABELS[$day])
                ->join(', ').'.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int>  $days
     */
    private function insertGroup(string $groupId, array $attributes, array $days): Schedule
    {
        $first = null;

        foreach ($days as $day) {
            $schedule = Schedule::query()->create([
                ...$attributes,
                'group_id' => $groupId,
                'day_of_week' => $day,
            ]);
            $first ??= $schedule;
        }

        return $first->load('groupSchedules');
    }
}
