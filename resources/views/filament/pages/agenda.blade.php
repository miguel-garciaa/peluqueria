<x-filament-panels::page>
    <div class="agenda-shell" wire:poll.5s>
        <div class="agenda-toolbar">
            <div class="agenda-navigation" aria-label="Navegación por semanas">
                <button type="button" wire:click="previousWeek" class="agenda-icon-button" title="Semana anterior" aria-label="Semana anterior">
                    <x-filament::icon icon="heroicon-o-chevron-left" />
                </button>
                <button type="button" wire:click="goToToday" class="agenda-today">Hoy</button>
                <button type="button" wire:click="nextWeek" class="agenda-icon-button" title="Semana siguiente" aria-label="Semana siguiente">
                    <x-filament::icon icon="heroicon-o-chevron-right" />
                </button>
                <strong>{{ $this->weekLabel() }}</strong>
            </div>

            <div class="agenda-filters">
                {{ $this->filters }}
            </div>
        </div>

        <div class="agenda-live" aria-live="polite">
            <span></span> Actualización automática cada 5 segundos
        </div>

        <div class="agenda-week">
            @foreach ($this->week() as $day)
                <section class="agenda-day {{ $day['is_today'] ? 'is-today' : '' }}">
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
