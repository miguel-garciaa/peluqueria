<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Horario semanal')->schema([
                    TextEntry::make('professional.name')->label('Profesional'),
                    TextEntry::make('days_label')->label('Días')->badge()->color('gray'),
                    TextEntry::make('starts_at')->label('Desde')->time('H:i'),
                    TextEntry::make('ends_at')->label('Hasta')->time('H:i'),
                    TextEntry::make('slot_interval_minutes')->label('Intervalo')->suffix(' min'),
                    IconEntry::make('is_active')->label('Disponible')->boolean(),
                ])->columns(3)->columnSpanFull(),
            ]);
    }
}
