<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['group_id', 'professional_id', 'day_of_week', 'starts_at', 'ends_at', 'slot_interval_minutes', 'is_active'])]
class Schedule extends Model
{
    public const array DAY_LABELS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        0 => 'Domingo',
    ];

    private const array SHORT_DAY_LABELS = [
        1 => 'Lun',
        2 => 'Mar',
        3 => 'Mié',
        4 => 'Jue',
        5 => 'Vie',
        6 => 'Sáb',
        0 => 'Dom',
    ];

    protected $table = 'professional_schedules';

    protected static function booted(): void
    {
        static::creating(function (Schedule $schedule): void {
            $schedule->group_id ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_interval_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function groupSchedules(): HasMany
    {
        return $this->hasMany(self::class, 'group_id', 'group_id');
    }

    /** @return array<int, int> */
    public function groupedDays(): array
    {
        if (blank($this->group_id)) {
            return [(int) $this->day_of_week];
        }

        $days = $this->relationLoaded('groupSchedules')
            ? $this->groupSchedules->pluck('day_of_week')
            : $this->groupSchedules()->pluck('day_of_week');

        return $days
            ->map(fn (mixed $day): int => (int) $day)
            ->unique()
            ->sortBy(fn (int $day): int => $day === 0 ? 7 : $day)
            ->values()
            ->all();
    }

    protected function daysLabel(): Attribute
    {
        return Attribute::get(function (): string {
            $days = $this->groupedDays();

            return match ($days) {
                [1, 2, 3, 4, 5, 6, 0] => 'Todos los días',
                [1, 2, 3, 4, 5, 6] => 'Lunes–Sábado',
                [1, 2, 3, 4, 5] => 'Lunes–Viernes',
                default => collect($days)
                    ->map(fn (int $day): string => self::SHORT_DAY_LABELS[$day])
                    ->join(', '),
            };
        });
    }
}
