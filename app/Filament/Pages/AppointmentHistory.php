<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Professional;
use App\Models\Service;
use App\Services\AppointmentHistoryReport;
use App\Services\CompleteElapsedAppointments;
use App\Support\AppointmentHistoryPeriod;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.appointment-history';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Historial';

    protected static ?string $title = 'Historial de clientes y citas';

    protected static ?int $navigationSort = 3;

    public string $period = 'month';

    public string $serviceFilter = 'all';

    public string $professionalFilter = 'all';

    public function mount(): void
    {
        app(CompleteElapsedAppointments::class)->handle();
    }

    public function filters(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('period')
                    ->label('Periodo')
                    ->options(AppointmentHistoryPeriod::options())
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable())
                    ->required(),
                Select::make('serviceFilter')
                    ->label('Servicio')
                    ->options(fn (): array => ['all' => 'Todos los servicios'] + Service::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->native(false)
                    ->searchable()
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                Select::make('professionalFilter')
                    ->label('Profesional')
                    ->options(fn (): array => ['all' => 'Todos los profesionales'] + Professional::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->native(false)
                    ->searchable()
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->historyQuery()->with([
                'user:id,email',
                'service:id,name',
                'professional:id,name',
                'payment:id,booking_id,method,status,amount,paid_at',
            ]))
            ->poll('30s')
            ->defaultSort('ends_at', 'desc')
            ->searchPlaceholder('Buscar cliente o referencia')
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i', timezone: config('app.business_timezone'))
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->description(fn (Appointment $record): string => $record->customer_phone)
                    ->searchable(['customer_name', 'customer_phone'])
                    ->sortable()
                    ->wrap(),
                TextColumn::make('user.email')
                    ->label('Correo')
                    ->searchable()
                    ->copyable()
                    ->visibleFrom('xl'),
                TextColumn::make('service.name')
                    ->label('Servicio')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('professional.name')
                    ->label('Profesional')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('duration')
                    ->label('Duración')
                    ->state(fn (Appointment $record): string => (int) $record->starts_at->diffInMinutes($record->ends_at).' min')
                    ->visibleFrom('md'),
                TextColumn::make('payment.method')
                    ->label('Pago')
                    ->badge()
                    ->placeholder('Pendiente')
                    ->formatStateUsing(fn (?string $state): string => Payment::methodOptions()[$state] ?? 'Pendiente')
                    ->description(function (Appointment $record): string {
                        $status = Payment::statusOptions()[$record->payment?->status] ?? 'Pendiente';
                        $amount = $record->payment?->amount ?? $record->payment_amount;

                        return $amount !== null
                            ? $status.' · '.number_format((float) $amount, 2, ',', '.').' €'
                            : $status.' · importe por valorar';
                    }),
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver cita')
                    ->iconButton()
                    ->tooltip('Ver cita')
                    ->url(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record])),
            ])
            ->recordUrl(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('No hay citas completadas en este periodo')
            ->emptyStateDescription('Las citas aparecerán aquí automáticamente cuando termine su duración prevista.')
            ->emptyStateIcon('heroicon-o-clock');
    }

    /** @return array{appointments: int, customers: int, services: int, collected: float, latest: ?string} */
    public function summary(): array
    {
        $query = $this->historyQuery();
        $latest = (clone $query)->max('ends_at');

        return [
            'appointments' => (clone $query)->count(),
            'customers' => (clone $query)->distinct()->count('user_id'),
            'services' => (clone $query)->distinct()->count('service_id'),
            'collected' => (float) (clone $query)
                ->join('payments', 'payments.booking_id', '=', 'bookings.id')
                ->where('payments.status', 'paid')
                ->sum('payments.amount'),
            'latest' => $latest
                ? CarbonImmutable::parse($latest)->timezone(config('app.business_timezone'))->locale('es')->translatedFormat('d M, H:i')
                : null,
        ];
    }

    public function periodLabel(): string
    {
        return $this->selectedPeriod()->label();
    }

    public function rangeLabel(): string
    {
        return $this->selectedPeriod()->rangeLabel($this->now());
    }

    public function refreshHistory(): void
    {
        if (app(CompleteElapsedAppointments::class)->handle() > 0) {
            $this->resetTable();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon(Heroicon::DocumentArrowDown)
                ->url(fn (): string => route('appointment-history.pdf', array_filter([
                    'period' => $this->period,
                    'service' => $this->selectedServiceId(),
                    'professional' => $this->selectedProfessionalId(),
                ], fn (mixed $value): bool => $value !== null))),
        ];
    }

    private function historyQuery(?CarbonImmutable $now = null): Builder
    {
        return app(AppointmentHistoryReport::class)->query(
            $this->selectedPeriod(),
            $now ?? $this->now(),
            $this->selectedServiceId(),
            $this->selectedProfessionalId(),
        );
    }

    private function selectedPeriod(): AppointmentHistoryPeriod
    {
        return AppointmentHistoryPeriod::fromValue($this->period);
    }

    private function selectedServiceId(): ?int
    {
        return $this->serviceFilter === 'all' ? null : (int) $this->serviceFilter;
    }

    private function selectedProfessionalId(): ?int
    {
        return $this->professionalFilter === 'all' ? null : (int) $this->professionalFilter;
    }

    private function now(): CarbonImmutable
    {
        return CarbonImmutable::now(config('app.business_timezone'));
    }
}
