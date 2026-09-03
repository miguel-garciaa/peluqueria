<?php

namespace App\Filament\Pages;

use App\Filament\Actions\CancelAppointmentAction;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Professional;
use App\Services\CompleteElapsedAppointments;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Agenda extends Page
{
    protected string $view = 'filament.pages.agenda';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?string $title = 'Agenda de citas';

    protected static ?int $navigationSort = 1;

    public string $selectedDate = '';

    public string $professionalFilter = 'all';

    public string $statusFilter = 'active';

    public function mount(): void
    {
        app(CompleteElapsedAppointments::class)->handle();
        $this->selectedDate = CarbonImmutable::now(config('app.business_timezone'))->format('Y-m-d');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newAppointment')
                ->label('Nueva cita')
                ->icon(Heroicon::Plus)
                ->url(AppointmentResource::getUrl('create')),
        ];
    }

    public function cancelAppointmentAction(): Action
    {
        return CancelAppointmentAction::make();
    }

    public function filters(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('professionalFilter')
                    ->label('Profesional')
                    ->options(fn (): array => ['all' => 'Todos'] + $this->professionals()->pluck('name', 'id')->all())
                    ->native(false)
                    ->live(),
                Select::make('statusFilter')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activas',
                        'all' => 'Todas',
                        'pending' => 'Pendientes',
                        'completed' => 'Completadas',
                        'cancelled' => 'Canceladas',
                    ])
                    ->native(false)
                    ->live(),
            ])
            ->columns(2);
    }

    public function previousWeek(): void
    {
        $this->selectedDate = $this->weekStart()->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->selectedDate = $this->weekStart()->addWeek()->format('Y-m-d');
    }

    public function goToToday(): void
    {
        $this->selectedDate = CarbonImmutable::now(config('app.business_timezone'))->format('Y-m-d');
    }

    /** @return Collection<int, Professional> */
    public function professionals(): Collection
    {
        return Professional::query()->select(['id', 'name'])->orderBy('name')->get();
    }

    /** @return array<int, array{date: CarbonImmutable, is_today: bool, appointments: Collection<int, Appointment>}> */
    public function week(): array
    {
        $timezone = config('app.business_timezone');
        $start = $this->weekStart();
        $end = $start->addWeek();

        $appointments = Appointment::query()
            ->select([
                'id', 'service_id', 'professional_id', 'customer_name', 'customer_phone',
                'starts_at', 'ends_at', 'status',
            ])
            ->with([
                'service:id,name',
                'professional:id,name',
            ])
            ->where('starts_at', '<', $end->utc())
            ->where('ends_at', '>', $start->utc())
            ->when($this->professionalFilter !== 'all', fn (Builder $query) => $query->where('professional_id', $this->professionalFilter))
            ->when($this->statusFilter === 'active', fn (Builder $query) => $query->whereIn('status', ['pending', 'confirmed']))
            ->when(! in_array($this->statusFilter, ['all', 'active'], true), fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Appointment $appointment): string => $appointment->starts_at->timezone($timezone)->format('Y-m-d'));

        return collect(range(0, 6))->map(function (int $offset) use ($appointments, $start, $timezone): array {
            $date = $start->addDays($offset);

            return [
                'date' => $date,
                'is_today' => $date->isSameDay(CarbonImmutable::now($timezone)),
                'appointments' => $appointments->get($date->format('Y-m-d'), collect()),
            ];
        })->all();
    }

    public function weekLabel(): string
    {
        $start = $this->weekStart();
        $end = $start->addDays(6);

        return ucfirst($start->locale('es')->translatedFormat('j M')).' – '.$end->locale('es')->translatedFormat('j M Y');
    }

    private function weekStart(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->selectedDate ?: 'today', config('app.business_timezone'))
            ->startOfWeek();
    }
}
