<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumen de la cita')->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('reference')->label('Referencia')->copyable(),
                        TextEntry::make('status')
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
                        TextEntry::make('starts_at')
                            ->label('Fecha y hora')
                            ->dateTime('d/m/Y H:i', timezone: config('app.business_timezone')),
                    ]),
                ])->columnSpanFull(),
                Section::make('Cliente')->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('customer_name')->label('Nombre'),
                        TextEntry::make('user.email')->label('Correo')->copyable(),
                        TextEntry::make('customer_phone')->label('Teléfono')->copyable(),
                    ]),
                ])->columnSpanFull(),
                Section::make('Atención')->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('service.name')->label('Servicio'),
                        TextEntry::make('professional.name')->label('Profesional'),
                        TextEntry::make('ends_at')
                            ->label('Final prevista')
                            ->dateTime('H:i', timezone: config('app.business_timezone')),
                    ]),
                    TextEntry::make('custom_details')
                        ->label('Detalles personalizados')
                        ->placeholder('Sin detalles adicionales'),
                ])->columnSpanFull(),
            ]);
    }
}
