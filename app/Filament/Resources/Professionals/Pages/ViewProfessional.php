<?php

namespace App\Filament\Resources\Professionals\Pages;

use App\Filament\Resources\Professionals\ProfessionalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProfessional extends ViewRecord
{
    protected static string $resource = ProfessionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
