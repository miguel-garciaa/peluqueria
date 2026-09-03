<?php

namespace App\Filament\Resources\ProfessionalCalendarEntries\Pages;

use App\Filament\Resources\ProfessionalCalendarEntries\ProfessionalCalendarEntryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProfessionalCalendarEntry extends ViewRecord
{
    protected static string $resource = ProfessionalCalendarEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
