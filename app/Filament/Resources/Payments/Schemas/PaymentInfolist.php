<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cobro')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('method')->label('Forma de pago')->badge()
                        ->formatStateUsing(fn (string $state): string => Payment::methodOptions()[$state] ?? $state),
                    TextEntry::make('status')->label('Estado')->badge()
                        ->formatStateUsing(fn (string $state): string => Payment::statusOptions()[$state] ?? $state),
                    TextEntry::make('amount')->label('Importe')->money('EUR', locale: 'es')->placeholder('Sin importe'),
                    TextEntry::make('paid_at')->label('Cobrado el')->dateTime('d/m/Y H:i', timezone: config('app.business_timezone')),
                    TextEntry::make('gateway_reference')->label('Referencia Redsys / Bizum')->placeholder('No aplica'),
                ]),
            ])->columnSpanFull(),
            Section::make('Cita asociada')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('appointment.customer_name')->label('Cliente'),
                    TextEntry::make('appointment.service.name')->label('Servicio'),
                    TextEntry::make('appointment.professional.name')->label('Profesional'),
                    TextEntry::make('appointment.starts_at')->label('Fecha y hora')->dateTime('d/m/Y H:i', timezone: config('app.business_timezone')),
                    TextEntry::make('appointment.reference')->label('Referencia de cita')->copyable(),
                ]),
            ])->columnSpanFull(),
        ]);
    }
}
