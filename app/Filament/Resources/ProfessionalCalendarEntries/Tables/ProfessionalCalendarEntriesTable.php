<?php

namespace App\Filament\Resources\ProfessionalCalendarEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProfessionalCalendarEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')->label('Fecha')->date('d/m/Y')->sortable(),
                TextColumn::make('professional.name')->label('Profesional')->searchable()->sortable(),
                TextColumn::make('type')->label('Tipo')->badge()->formatStateUsing(fn (string $state): string => $state === 'blocked' ? 'Bloqueo' : 'Apertura')->color(fn (string $state): string => $state === 'blocked' ? 'danger' : 'success'),
                IconColumn::make('all_day')->label('Día completo')->boolean(),
                TextColumn::make('starts_at')->label('Desde')->time('H:i')->placeholder('—'),
                TextColumn::make('ends_at')->label('Hasta')->time('H:i')->placeholder('—'),
                TextColumn::make('reason')->label('Motivo')->limit(35),
            ])
            ->filters([
                SelectFilter::make('professional')->relationship('professional', 'name')->label('Profesional')->native(false),
                SelectFilter::make('type')->label('Tipo')->options(['blocked' => 'Bloqueo', 'available' => 'Apertura'])->native(false),
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
