<?php

namespace App\Filament\Resources\ProfessionalCalendarEntries\Pages;

use App\Filament\Resources\ProfessionalCalendarEntries\ProfessionalCalendarEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProfessionalCalendarEntry extends EditRecord
{
    protected static string $resource = ProfessionalCalendarEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
