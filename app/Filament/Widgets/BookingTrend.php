<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class BookingTrend extends ChartWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Reservas de los últimos 30 días';

    protected ?string $description = 'Nuevas reservas y cancelaciones por día.';

    protected ?string $pollingInterval = '15s';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];

    protected function getData(): array
    {
        $timezone = config('app.business_timezone');
        $start = CarbonImmutable::now($timezone)->startOfDay()->subDays(29);
        $days = collect(range(0, 29))->map(fn (int $offset) => $start->addDays($offset));
        $appointments = Appointment::query()
            ->where(function ($query) use ($start): void {
                $query->where('created_at', '>=', $start->utc())
                    ->orWhere('cancelled_at', '>=', $start->utc());
            })
            ->get(['created_at', 'status', 'cancelled_at']);

        return [
            'datasets' => [
                [
                    'label' => 'Reservas',
                    'data' => $days->map(fn (CarbonImmutable $day): int => $appointments
                        ->filter(fn (Appointment $appointment): bool => $appointment->created_at->timezone($timezone)->isSameDay($day))
                        ->count())->all(),
                    'borderColor' => '#b7791f',
                    'backgroundColor' => 'rgba(183, 121, 31, .16)',
                    'fill' => true,
                    'tension' => .3,
                ],
                [
                    'label' => 'Cancelaciones',
                    'data' => $days->map(fn (CarbonImmutable $day): int => $appointments
                        ->filter(fn (Appointment $appointment): bool => $appointment->cancelled_at?->timezone($timezone)->isSameDay($day) ?? false)
                        ->count())->all(),
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220, 38, 38, .08)',
                    'tension' => .3,
                ],
            ],
            'labels' => $days->map(fn (CarbonImmutable $day): string => $day->format('d/m'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
