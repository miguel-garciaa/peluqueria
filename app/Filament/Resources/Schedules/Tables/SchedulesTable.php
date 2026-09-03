<?php

namespace App\Filament\Resources\Schedules\Tables;

use App\Models\Schedule;
use App\Services\ManageScheduleGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Semana habitual')
            ->description('Cada fila reúne los días que comparten profesional, franja e intervalo.')
            ->columns([
                TextColumn::make('professional.name')
                    ->label('Profesional')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('days_label')
                    ->label('Días')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('time_range')
                    ->label('Horario')
                    ->state(fn (Schedule $record): string => substr((string) $record->starts_at, 0, 5).'–'.substr((string) $record->ends_at, 0, 5)),
                TextColumn::make('slot_interval_minutes')
                    ->label('Citas cada')
                    ->suffix(' min'),
                IconColumn::make('is_active')
                    ->label('Disponible')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('professional')
                    ->relationship('professional', 'name')
                    ->label('Profesional')
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()
                    ->modalHeading('Borrar horario semanal')
                    ->modalDescription('Se eliminarán todos los días incluidos en este bloque.')
                    ->using(fn (Schedule $record): bool => app(ManageScheduleGroup::class)->delete($record)),
            ])
            ->emptyStateHeading('Todavía no hay horarios semanales')
            ->emptyStateDescription('Crea un bloque, selecciona varios días y asígnalo a un profesional.')
            ->defaultPaginationPageOption(25);
    }
}
