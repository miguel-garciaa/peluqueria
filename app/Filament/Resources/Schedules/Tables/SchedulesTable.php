<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('professional.name')->label('Profesional')->searchable()->sortable(),
                TextColumn::make('day_of_week')->label('Día')->formatStateUsing(fn (int $state): string => [
                    0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                    4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
                ][$state])->sortable(),
                TextColumn::make('starts_at')->label('Desde')->time('H:i'),
                TextColumn::make('ends_at')->label('Hasta')->time('H:i'),
                TextColumn::make('slot_interval_minutes')->label('Intervalo')->suffix(' min'),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([
                SelectFilter::make('professional')->relationship('professional', 'name')->label('Profesional'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
