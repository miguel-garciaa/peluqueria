<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Horario semanal')->schema([
                    Select::make('professional_id')->label('Profesional')->relationship('professional', 'name')->searchable()->preload()->required(),
                    Select::make('day_of_week')->label('Día')->options([
                        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
                        5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo',
                    ])->required(),
                    TimePicker::make('starts_at')->label('Apertura')->seconds(false)->minutesStep(15)->required(),
                    TimePicker::make('ends_at')->label('Cierre')->seconds(false)->minutesStep(15)->after('starts_at')->required(),
                    TextInput::make('slot_interval_minutes')->label('Intervalo')->integer()->minValue(15)->maxValue(240)->step(15)->suffix('minutos')->default(30)->required(),
                    Toggle::make('is_active')->label('Horario activo')->default(true),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
