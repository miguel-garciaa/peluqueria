<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('paid_at', 'desc')
            ->poll('30s')
            ->searchPlaceholder('Buscar cliente o referencia')
            ->columns([
                TextColumn::make('paid_at')
                    ->label('Cobrado el')
                    ->dateTime('d/m/Y H:i', timezone: config('app.business_timezone'))
                    ->placeholder('Pendiente')
                    ->sortable(),
                TextColumn::make('appointment.customer_name')
                    ->label('Cliente')
                    ->description(fn (Payment $record): string => $record->appointment->customer_phone)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('appointment.service.name')->label('Servicio')->searchable()->sortable(),
                TextColumn::make('method')
                    ->label('Forma de pago')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Payment::methodOptions()[$state] ?? $state),
                TextColumn::make('amount')->label('Importe')->money('EUR', locale: 'es')->placeholder('Sin importe')->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Payment::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('appointment.professional.name')->label('Profesional')->visibleFrom('lg'),
                TextColumn::make('gateway_reference')->label('Referencia Redsys')->searchable()->copyable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('method')->label('Forma de pago')->options(Payment::methodOptions())->native(false),
                SelectFilter::make('status')->label('Estado')->options(Payment::statusOptions())->native(false),
                SelectFilter::make('professional')
                    ->relationship('appointment.professional', 'name')
                    ->label('Profesional')
                    ->native(false),
                SelectFilter::make('service')
                    ->relationship('appointment.service', 'name')
                    ->label('Servicio')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->iconButton()->tooltip('Ver pago'),
                EditAction::make()->iconButton()->tooltip('Editar pago'),
            ])
            ->recordUrl(fn (Payment $record): string => PaymentResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Todavía no hay pagos registrados')
            ->emptyStateDescription('Los pagos en efectivo aparecerán al terminar la cita; Bizum aparecerá al recibir la confirmación de Redsys.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
