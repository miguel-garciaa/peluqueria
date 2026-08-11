<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cliente')
                    ->description('Datos de contacto asociados a la reserva.')
                    ->schema([
                        Select::make('user_id')
                            ->label('Cliente registrado')
                            ->relationship('user', 'name')
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => "{$record->name} · {$record->email}")
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?int $state): void {
                                $user = $state ? User::query()->find($state) : null;
                                $set('customer_name', $user?->name);
                                $set('customer_phone', $user?->phone);
                            })
                            ->required(),
                        Grid::make(2)->schema([
                            TextInput::make('customer_name')
                                ->label('Nombre completo')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('customer_phone')
                                ->label('Teléfono')
                                ->tel()
                                ->telRegex('/^\+34 [6789]\d{2}(?: \d{2}){3}$/')
                                ->mask('+34 999 99 99 99')
                                ->placeholder('+34 600 00 00 00')
                                ->required()
                                ->maxLength(16),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Servicio y profesional')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('service_id')
                                ->label('Servicio')
                                ->relationship('service', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('name'))
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('professional_id')
                                ->label('Profesional')
                                ->relationship('professional', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('name'))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                        Textarea::make('custom_details')
                            ->label('Detalles del servicio personalizado')
                            ->rows(3)
                            ->maxLength(100)
                            ->helperText('Máximo 100 caracteres.'),
                    ])
                    ->columnSpanFull(),
                Section::make('Fecha y estado')
                    ->schema([
                        Grid::make(3)->schema([
                            DatePicker::make('appointment_date')
                                ->label('Fecha')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->weekStartsOnMonday()
                                ->required(),
                            TimePicker::make('appointment_time')
                                ->label('Hora')
                                ->native(false)
                                ->seconds(false)
                                ->minutesStep(30)
                                ->required(),
                            Select::make('status')
                                ->label('Estado')
                                ->options([
                                    'pending' => 'Pendiente',
                                    'confirmed' => 'Confirmada',
                                    'completed' => 'Completada',
                                    'cancelled' => 'Cancelada',
                                ])
                                ->default('confirmed')
                                ->required(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
