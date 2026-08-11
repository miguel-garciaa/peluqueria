<?php

namespace App\Filament\Resources\ProfessionalCalendarEntries\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfessionalCalendarEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Excepción')->schema([
                    TextEntry::make('professional.name')->label('Profesional'),
                    TextEntry::make('date')->label('Fecha')->date('d/m/Y'),
                    TextEntry::make('type')->label('Tipo')->badge()->formatStateUsing(fn (string $state): string => $state === 'blocked' ? 'Bloqueo' : 'Apertura'),
                    IconEntry::make('all_day')->label('Día completo')->boolean(),
                    TextEntry::make('starts_at')->label('Desde')->time('H:i')->placeholder('—'),
                    TextEntry::make('ends_at')->label('Hasta')->time('H:i')->placeholder('—'),
                    TextEntry::make('reason')->label('Motivo')->placeholder('Sin nota'),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
