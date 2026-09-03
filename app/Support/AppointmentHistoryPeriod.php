<?php

namespace App\Support;

use Carbon\CarbonImmutable;

enum AppointmentHistoryPeriod: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Quarter = 'quarter';
    case All = 'all';

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $period): array => [$period->value => $period->label()])
            ->all();
    }

    public static function fromValue(string $value): self
    {
        return self::tryFrom($value) ?? self::Month;
    }

    public function label(): string
    {
        return match ($this) {
            self::Day => 'Últimas 24 horas',
            self::Week => 'Últimos 7 días',
            self::Month => 'Últimos 30 días',
            self::Quarter => 'Últimos 3 meses',
            self::All => 'Histórico completo',
        };
    }

    public function fileLabel(): string
    {
        return match ($this) {
            self::Day => 'ultimo-dia',
            self::Week => 'ultima-semana',
            self::Month => 'ultimo-mes',
            self::Quarter => 'ultimos-3-meses',
            self::All => 'global',
        };
    }

    public function startsAt(CarbonImmutable $now): ?CarbonImmutable
    {
        return match ($this) {
            self::Day => $now->subDay(),
            self::Week => $now->subWeek(),
            self::Month => $now->subMonth(),
            self::Quarter => $now->subMonths(3),
            self::All => null,
        };
    }

    public function rangeLabel(CarbonImmutable $now): string
    {
        $start = $this->startsAt($now);

        if (! $start) {
            return 'Desde la primera cita completada hasta '.$now->locale('es')->translatedFormat('d/m/Y H:i');
        }

        return $start->locale('es')->translatedFormat('d/m/Y H:i')
            .' - '.$now->locale('es')->translatedFormat('d/m/Y H:i');
    }
}
