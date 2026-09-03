<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Añadir producto';

    protected static ?string $breadcrumb = 'Nuevo';

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Crear producto');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Crear y añadir otro');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Cancelar');
    }
}
