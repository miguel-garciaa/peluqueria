<?php

namespace App\Filament\Resources\ProfessionalCalendarEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProfessionalCalendarEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Excepción de agenda')->description('Bloquea vacaciones o abre un tramo extraordinario.')->schema([
                    Select::make('professional_id')->label('Profesional')->relationship('professional', 'name')->searchable()->preload()->required(),
                    DatePicker::make('date')->label('Fecha')->native(false)->displayFormat('d/m/Y')->required(),
                    Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'blocked' => 'Horario bloqueado',
                            'available' => 'Apertura extraordinaria',
                        ])
                        ->default('blocked')
                        ->live()
                        ->afterStateUpdated(function (Set $set, string $state): void {
                            if ($state === 'available') {
                                $set('all_day', false);
                            }
                        })
                        ->required(),
                    Toggle::make('all_day')->label('Día completo')->default(true)->live()->visible(fn (Get $get): bool => $get('type') === 'blocked'),
                    TimePicker::make('starts_at')->label('Desde')->seconds(false)->minutesStep(15)->required(fn (Get $get): bool => ! $get('all_day'))->visible(fn (Get $get): bool => ! $get('all_day')),
                    TimePicker::make('ends_at')->label('Hasta')->seconds(false)->minutesStep(15)->after('starts_at')->required(fn (Get $get): bool => ! $get('all_day'))->visible(fn (Get $get): bool => ! $get('all_day')),
                    TextInput::make('slot_interval_minutes')->label('Intervalo')->integer()->minValue(15)->maxValue(240)->step(15)->suffix('minutos')->default(30)->visible(fn (Get $get): bool => $get('type') === 'available'),
                    TextInput::make('reason')->label('Motivo o nota')->maxLength(255)->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
