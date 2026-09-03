<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\FileUpload;
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
                    FileUpload::make('image_path')
                        ->label('Fotografía del servicio')
                        ->disk('public')
                        ->directory('catalog/services')
                        ->visibility('public')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->imageEditor()
                        ->imageEditorAspectRatioOptions(['4:3', '1:1'])
                        ->imagePreviewHeight('240')
                        ->openable()
                        ->helperText('JPG, PNG o WebP. Máximo 5 MB. Recomendado: formato horizontal 4:3.')
                        ->columnSpanFull(),
                    TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                    TextInput::make('slug')->label('Identificador')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(50),
                    Textarea::make('description')->label('Descripción')->rows(3)->maxLength(1000)->columnSpanFull(),
                    TextInput::make('duration_minutes')->label('Duración')->integer()->minValue(15)->maxValue(720)->step(15)->suffix('minutos')->required(),
                    TextInput::make('price_from')->label('Precio desde')->numeric()->prefix('€')->minValue(0)->maxValue(999999.99),
                    Toggle::make('is_custom')->label('Servicio personalizado'),
                    Toggle::make('is_active')->label('Disponible para reservas')->default(true),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
