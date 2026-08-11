<?php

namespace App\Filament\Resources\Professionals\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfessionalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos profesionales')->schema([
                    FileUpload::make('image_path')
                        ->label('Fotografía del profesional')
                        ->disk('public')
                        ->directory('catalog/professionals')
                        ->visibility('public')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->imageEditor()
                        ->imageEditorAspectRatioOptions(['4:5', '1:1'])
                        ->imagePreviewHeight('280')
                        ->openable()
                        ->helperText('JPG, PNG o WebP. Máximo 5 MB. Recomendado: retrato vertical 4:5.')
                        ->columnSpanFull(),
                    TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                    TextInput::make('slug')->label('Identificador')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(50),
                    TextInput::make('role')->label('Especialidad')->maxLength(255),
                    Toggle::make('is_active')->label('Disponible para reservas')->default(true),
                    CheckboxList::make('services')
                        ->label('Servicios que realiza')
                        ->relationship('services', 'name')
                        ->columns(2)
                        ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
