<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Widgets\ProductInventoryStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected static ?string $breadcrumb = 'Inventario';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Añadir producto'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductInventoryStats::class,
        ];
    }
}
