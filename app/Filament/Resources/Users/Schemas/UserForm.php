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
                    TextInput::make('phone')->label('Teléfono')->tel()->maxLength(32),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
