<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Filament\Actions\CancelAppointmentAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i', timezone: config('app.business_timezone'))
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->description(fn ($record): string => $record->customer_phone)
                    ->searchable(),
                TextColumn::make('service.name')->label('Servicio')->sortable()->searchable(),
                TextColumn::make('professional.name')->label('Profesional')->sortable()->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reference')->label('Referencia')->toggleable(isToggledHiddenByDefault: true)->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->native(false)
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ]),
                SelectFilter::make('professional')->relationship('professional', 'name')->label('Profesional')->native(false),
                SelectFilter::make('service')->relationship('service', 'name')->label('Servicio')->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar cita definitivamente')
                    ->modalDescription('Esta acción borrará la cita y su historial de forma permanente. No se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar cita'),
                CancelAppointmentAction::make(),
            ]);
    }
}
