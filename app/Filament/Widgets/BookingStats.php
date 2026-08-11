<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BookingStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '5s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = now();
        $businessNow = now(config('app.business_timezone'));
        $todayStart = $businessNow->copy()->startOfDay()->utc();
        $todayEnd = $businessNow->copy()->addDay()->startOfDay()->utc();

        $counts = Appointment::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN status IN ('pending', 'confirmed') AND starts_at >= ? THEN 1 ELSE 0 END), 0) AS active",
                [$now],
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN status IN ('pending', 'confirmed') AND starts_at >= ? AND starts_at < ? THEN 1 ELSE 0 END), 0) AS today",
                [$todayStart, $todayEnd],
            )
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) AS cancelled")
            ->firstOrFail();

        $configuredAdminEmail = Str::lower(trim((string) config('admin.email')));
        $customers = User::query()
            ->where('is_admin', false)
            ->when(
                $configuredAdminEmail !== '',
                fn (Builder $query): Builder => $query->whereRaw('LOWER(email) <> ?', [$configuredAdminEmail]),
            )
            ->count();
        $active = (int) $counts->active;
        $today = (int) $counts->today;
        $total = (int) $counts->total;
        $cancelled = (int) $counts->cancelled;

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
