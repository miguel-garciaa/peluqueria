<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductInventoryStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected int|array|null $columns = [
        'default' => 2,
        '@xl' => 4,
        '!@lg' => 4,
    ];

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $products = Product::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_active = ? THEN 1 ELSE 0 END), 0) AS active', [true])
            ->selectRaw('COALESCE(SUM(units), 0) AS units')
            ->selectRaw('COALESCE(SUM(price * units), 0) AS inventory_value')
            ->firstOrFail();

        $lowStock = Product::query()->active()->lowStock()->count();
        $outOfStock = Product::query()->active()->where('units', 0)->count();

        return [
            Stat::make('Referencias', number_format((int) $products->total, 0, ',', '.'))
                ->description(number_format((int) $products->active, 0, ',', '.').' en uso')
                ->descriptionIcon(Heroicon::ArchiveBox, IconPosition::Before)
                ->color('primary'),
            Stat::make('Unidades disponibles', number_format((int) $products->units, 0, ',', '.'))
                ->description('Suma de todo el inventario')
                ->descriptionIcon(Heroicon::Cube, IconPosition::Before)
                ->color('info'),
            Stat::make('Necesitan reposición', number_format($lowStock, 0, ',', '.'))
                ->description(number_format($outOfStock, 0, ',', '.').' sin existencias')
                ->descriptionIcon(Heroicon::ExclamationTriangle, IconPosition::Before)
                ->color($lowStock > 0 ? 'warning' : 'success'),
            Stat::make('Valor del inventario', number_format((float) $products->inventory_value, 2, ',', '.').' €')
                ->description('Según el precio registrado')
                ->descriptionIcon(Heroicon::Banknotes, IconPosition::Before)
                ->color('success'),
        ];
    }
}
