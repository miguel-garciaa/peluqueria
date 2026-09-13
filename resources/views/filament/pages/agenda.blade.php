<x-filament-panels::page>
    <div class="agenda-shell" wire:poll.5s>
        <div class="agenda-toolbar">
            <div class="agenda-toolbar-main">
                <div class="agenda-view-toggle" role="group" aria-label="Vista del calendario">
                    <button
                        type="button"
                        wire:click="setCalendarView('week')"
                        class="{{ $calendarView === 'week' ? 'is-active' : '' }}"
                        aria-pressed="{{ $calendarView === 'week' ? 'true' : 'false' }}"
                    >Semana</button>
                    <button
                        type="button"
                        wire:click="setCalendarView('month')"
                        class="{{ $calendarView === 'month' ? 'is-active' : '' }}"
                        aria-pressed="{{ $calendarView === 'month' ? 'true' : 'false' }}"
                    >Mes</button>
                </div>

                <div class="agenda-navigation" aria-label="Navegación del calendario">
                    <button type="button" wire:click="previousPeriod" class="agenda-icon-button" title="Periodo anterior" aria-label="Periodo anterior">
                        <x-filament::icon icon="heroicon-o-chevron-left" />
                    </button>
                    <button type="button" wire:click="goToToday" class="agenda-today">Hoy</button>
                    <button type="button" wire:click="nextPeriod" class="agenda-icon-button" title="Periodo siguiente" aria-label="Periodo siguiente">
                        <x-filament::icon icon="heroicon-o-chevron-right" />
                    </button>
                    <strong>{{ $this->periodLabel() }}</strong>
                </div>
            </div>

            <div class="agenda-filters">
                {{ $this->filters }}
            </div>
        </div>

        <div class="agenda-live" aria-live="polite">
            <span></span> Actualización automática cada 5 segundos
        </div>

        <div class="agenda-calendar is-{{ $calendarView }}">
            @foreach ($this->calendarDays() as $day)
                <section
                    wire:key="agenda-day-{{ $day['date']->format('Y-m-d') }}"
                    class="agenda-day {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_current_month'] ? '' : 'is-outside-month' }}"
                >
                    <header>
                        <span>{{ ucfirst($day['date']->locale('es')->translatedFormat('D')) }}</span>
                        <strong>{{ $day['date']->format('d') }}</strong>
                        <small>{{ $day['appointments']->count() }} {{ $day['appointments']->count() === 1 ? 'cita' : 'citas' }}</small>
                    </header>

                    <div class="agenda-events">
                        @forelse ($day['appointments'] as $appointment)
                            @php($localStart = $appointment->starts_at->timezone(config('app.business_timezone')))
                            <article class="agenda-event status-{{ $appointment->status }}">
                                <a
                                    href="{{ \App\Filament\Resources\Appointments\AppointmentResource::getUrl('view', ['record' => $appointment]) }}"
                                    class="agenda-event-link"
                                >
                                    <div class="agenda-event-top">
                                        <time>{{ $localStart->format('H:i') }}</time>
                                        <span>{{ match ($appointment->status) {
                                            'pending' => 'Pendiente',
                                            'confirmed' => 'Confirmada',
                                            'completed' => 'Completada',
                                            'cancelled' => 'Cancelada',
                                            default => $appointment->status,
                                        } }}</span>
                                    </div>
                                    <strong>{{ $appointment->customer_name }}</strong>
                                    <small>{{ $appointment->service->name }}</small>
                                    <small>{{ $appointment->professional->name }}</small>
                                    <small class="agenda-phone">{{ $appointment->customer_phone }}</small>
                                </a>

                                @if ($appointment->canBeCancelled())
                                    <div class="agenda-event-actions">
                                        {{ ($this->cancelAppointmentAction)(['appointment' => $appointment->getKey()]) }}
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="agenda-empty">Sin citas</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
