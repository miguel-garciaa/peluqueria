<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Schedule;
use Filament\Forms\Components\CheckboxList;
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
                Section::make('Disponibilidad semanal')
                    ->description('Define una franja y aplícala a todos los días que compartan el mismo horario.')
                    ->schema([
                        Select::make('professional_id')
                            ->label('Profesional')
                            ->relationship('professional', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        CheckboxList::make('days_of_week')
                            ->label('Días de trabajo')
                            ->helperText('Marca varios días para gestionarlos como un único bloque semanal.')
                            ->options(Schedule::DAY_LABELS)
                            ->columns(['default' => 2, 'lg' => 4])
                            ->bulkToggleable()
                            ->required()
                            ->minItems(1)
                            ->columnSpanFull(),
                        TimePicker::make('starts_at')
                            ->label('Desde')
                            ->seconds(false)
                            ->minutesStep(15)
                            ->required(),
                        TimePicker::make('ends_at')
                            ->label('Hasta')
                            ->seconds(false)
                            ->minutesStep(15)
                            ->after('starts_at')
                            ->required(),
                        TextInput::make('slot_interval_minutes')
                            ->label('Intervalo de citas')
                            ->integer()
                            ->minValue(15)
                            ->maxValue(240)
                            ->step(15)
                            ->suffix('minutos')
                            ->default(30)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Disponible para reservas')
                            ->helperText('Puedes desactivarlo temporalmente sin borrarlo.')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
