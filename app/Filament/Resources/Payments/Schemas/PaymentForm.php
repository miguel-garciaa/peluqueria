<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pago')
                ->description('Corrige únicamente un cobro que ya exista. Las nuevas operaciones se crean desde la cita o la futura confirmación de Redsys.')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('method')
                            ->label('Forma de pago')
                            ->options(Payment::methodOptions())
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('status')
                            ->label('Estado')
                            ->options(Payment::statusOptions())
                            ->native(false)
                            ->required(),
                        TextInput::make('amount')
                            ->label('Importe')
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->step(0.01),
                        DateTimePicker::make('paid_at')
                            ->label('Fecha de cobro')
                            ->native(false)
                            ->seconds(false)
                            ->timezone(config('app.business_timezone')),
                    ]),
                    TextInput::make('gateway_reference')
                        ->label('Referencia Redsys / Bizum')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('No aplica al efectivo')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }
}
