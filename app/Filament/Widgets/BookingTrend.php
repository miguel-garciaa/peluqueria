<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class BookingTrend extends ChartWidget
{
    protected static bool $isLazy = true;

    protected ?string $heading = 'Reservas de los últimos 30 días';

    protected ?string $description = 'Nuevas reservas y cancelaciones por día.';

    protected ?string $pollingInterval = '15s';

    protected ?string $maxHeight = '18rem';

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
            ->get(['created_at', 'cancelled_at']);

        $reservationsByDay = array_fill_keys($days->map->format('Y-m-d')->all(), 0);
        $cancellationsByDay = $reservationsByDay;

        foreach ($appointments as $appointment) {
            $createdKey = $appointment->created_at->timezone($timezone)->format('Y-m-d');
            if (array_key_exists($createdKey, $reservationsByDay)) {
                $reservationsByDay[$createdKey]++;
            }

            if ($appointment->cancelled_at) {
                $cancelledKey = $appointment->cancelled_at->timezone($timezone)->format('Y-m-d');
                if (array_key_exists($cancelledKey, $cancellationsByDay)) {
                    $cancellationsByDay[$cancelledKey]++;
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Reservas',
                    'data' => array_values($reservationsByDay),
                    'borderColor' => '#b7791f',
                    'backgroundColor' => 'rgba(183, 121, 31, .16)',
                    'fill' => true,
                    'tension' => .3,
                ],
                [
                    'label' => 'Cancelaciones',
                    'data' => array_values($cancellationsByDay),
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
