<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Servicio')->schema([
                    TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                    TextInput::make('slug')->label('Identificador')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(50),
                    Textarea::make('description')->label('Descripción')->rows(3)->columnSpanFull(),
                    TextInput::make('duration_minutes')->label('Duración')->numeric()->minValue(15)->step(15)->suffix('minutos')->required(),
                    TextInput::make('price_from')->label('Precio desde')->numeric()->prefix('€')->minValue(0),
                    Toggle::make('is_custom')->label('Servicio personalizado'),
                    Toggle::make('is_active')->label('Disponible para reservas')->default(true),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
