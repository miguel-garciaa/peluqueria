<?php

namespace App\Filament\Resources\Professionals\Pages;

use App\Filament\Resources\Professionals\ProfessionalResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProfessional extends EditRecord
{
    protected static string $resource = ProfessionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
