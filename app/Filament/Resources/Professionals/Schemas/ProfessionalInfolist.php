<?php

namespace App\Filament\Resources\Professionals\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfessionalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profesional')->schema([
                    ImageEntry::make('image_path')
                        ->label('Fotografía')
                        ->disk('public')
                        ->height(280)
                        ->columnSpanFull(),
                    TextEntry::make('name')->label('Nombre'),
                    TextEntry::make('role')->label('Especialidad')->placeholder('Sin especialidad'),
                    IconEntry::make('is_active')->label('Activo')->boolean(),
                    TextEntry::make('services.name')->label('Servicios')->badge(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
