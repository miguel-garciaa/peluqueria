<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cliente')->schema([
                    TextEntry::make('name')->label('Nombre'),
                    TextEntry::make('email')->label('Correo')->copyable(),
                    TextEntry::make('phone')->label('Teléfono')->copyable()->placeholder('Sin teléfono'),
                    TextEntry::make('appointments_count')->label('Reservas realizadas')->state(fn ($record): int => $record->appointments()->count()),
                    TextEntry::make('created_at')->label('Registrado')->dateTime('d/m/Y H:i', timezone: config('app.business_timezone')),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
