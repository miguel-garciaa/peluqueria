<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingBookings extends TableWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Próximas citas')
            ->description('Las siguientes reservas pendientes o confirmadas.')
            ->poll('5s')
            ->query(fn (): Builder => Appointment::query()
                ->with([
                    'service:id,name',
                    'professional:id,name',
                ])
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at'))
            ->columns([
                TextColumn::make('starts_at')->label('Fecha')->dateTime('d/m H:i', timezone: config('app.business_timezone')),
                TextColumn::make('customer_name')->label('Cliente')->searchable(),
                TextColumn::make('service.name')->label('Servicio'),
                TextColumn::make('professional.name')->label('Profesional'),
            ])
            ->recordActions([
                ViewAction::make()->url(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record])),
            ])
            ->recordUrl(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record]))
            ->paginated([5, 10]);
    }
}
