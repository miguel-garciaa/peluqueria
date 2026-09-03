<?php

namespace App\Filament\Resources\ProfessionalCalendarEntries\Pages;

use App\Filament\Resources\ProfessionalCalendarEntries\ProfessionalCalendarEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfessionalCalendarEntries extends ListRecords
{
    protected static string $resource = ProfessionalCalendarEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
