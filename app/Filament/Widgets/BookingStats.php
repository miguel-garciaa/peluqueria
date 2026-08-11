<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '5s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = now();
        $active = Appointment::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('starts_at', '>=', $now)
            ->count();
        $today = Appointment::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('starts_at', [
                now(config('app.business_timezone'))->startOfDay()->utc(),
                now(config('app.business_timezone'))->endOfDay()->utc(),
            ])
            ->count();
        $customers = User::query()->where('is_admin', false)->count();
        $total = Appointment::query()->count();
        $cancelled = Appointment::query()->where('status', 'cancelled')->count();

        return [
            Stat::make('Reservas activas', number_format($active, 0, ',', '.'))
                ->description("{$today} para hoy")
                ->descriptionIcon(Heroicon::CalendarDays)
                ->color('success'),
            Stat::make('Clientes registrados', number_format($customers, 0, ',', '.'))
                ->description('Cuentas no administrativas')
                ->descriptionIcon(Heroicon::Users)
                ->color('primary'),
            Stat::make('Reservas realizadas', number_format($total, 0, ',', '.'))
                ->description('Histórico completo')
                ->descriptionIcon(Heroicon::ChartBar)
                ->color('info'),
            Stat::make('Reservas canceladas', number_format($cancelled, 0, ',', '.'))
                ->description($total > 0 ? number_format(($cancelled / $total) * 100, 1, ',', '.').' % del total' : '0 % del total')
                ->descriptionIcon(Heroicon::NoSymbol)
                ->color('danger'),
        ];
    }
}
