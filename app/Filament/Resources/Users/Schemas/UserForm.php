<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del cliente')->schema([
                    TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                    TextInput::make('email')->label('Correo')->email()->disabled(),
                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->telRegex('/^\+34 [6789]\d{2}(?: \d{2}){3}$/')
                        ->mask('+34 999 99 99 99')
                        ->placeholder('+34 600 00 00 00')
                        ->maxLength(16),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
