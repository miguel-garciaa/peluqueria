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
                Section::make('Horario')->schema([
                    TextEntry::make('professional.name')->label('Profesional'),
                    TextEntry::make('day_of_week')->label('Día')->formatStateUsing(fn (int $state): string => [
                        0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
                    ][$state]),
                    TextEntry::make('starts_at')->label('Desde')->time('H:i'),
                    TextEntry::make('ends_at')->label('Hasta')->time('H:i'),
                    TextEntry::make('slot_interval_minutes')->label('Intervalo')->suffix(' min'),
                    IconEntry::make('is_active')->label('Activo')->boolean(),
                ])->columns(3)->columnSpanFull(),
            ]);
    }
}
