<?php

namespace App\Filament\Resources\Professionals\Tables;

use App\Models\Professional;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProfessionalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('role')->label('Especialidad')->searchable(),
                TextColumn::make('services.name')->label('Servicios')->badge()->limitList(3),
                TextColumn::make('appointments_count')->counts('appointments')->label('Citas')->sortable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar profesional')
                    ->modalDescription('Se eliminarán también sus horarios y excepciones de calendario. Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar profesional')
                    ->disabled(fn (Professional $record): bool => (int) $record->appointments_count > 0)
                    ->tooltip(fn (Professional $record): ?string => (int) $record->appointments_count > 0
                        ? 'No se puede eliminar porque tiene citas asociadas.'
                        : null),
            ]);
    }
}
