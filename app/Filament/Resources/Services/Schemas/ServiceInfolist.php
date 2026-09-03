<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Servicio')->schema([
                    ImageEntry::make('image_path')
                        ->label('Fotografía')
                        ->disk('public')
                        ->height(240)
                        ->columnSpanFull(),
                    TextEntry::make('name')->label('Nombre'),
                    TextEntry::make('description')->label('Descripción')->placeholder('Sin descripción'),
                    TextEntry::make('duration_minutes')->label('Duración')->suffix(' min'),
                    TextEntry::make('price_from')->label('Precio desde')->money('EUR')->placeholder('A consultar'),
                    IconEntry::make('is_custom')->label('Personalizado')->boolean(),
                    IconEntry::make('is_active')->label('Activo')->boolean(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
