<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->height(48),
                TextColumn::make('name')->label('Servicio')->searchable()->sortable(),
                TextColumn::make('duration_minutes')->label('Duración')->suffix(' min')->sortable(),
                TextColumn::make('price_from')->label('Precio desde')->money('EUR')->placeholder('A consultar')->sortable(),
                TextColumn::make('professionals_count')->counts('professionals')->label('Profesionales'),
                IconColumn::make('is_custom')->label('Personalizado')->boolean(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
