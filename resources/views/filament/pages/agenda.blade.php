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
                <label>
                    <span>Profesional</span>
                    <select wire:model.live="professionalFilter">
                        <option value="all">Todos</option>
                        @foreach ($this->professionals() as $professional)
                            <option value="{{ $professional->getKey() }}">{{ $professional->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Estado</span>
                    <select wire:model.live="statusFilter">
                        <option value="active">Activas</option>
                        <option value="all">Todas</option>
                        <option value="pending">Pendientes</option>
                        <option value="confirmed">Confirmadas</option>
                        <option value="completed">Completadas</option>
                        <option value="cancelled">Canceladas</option>
                    </select>
                </label>
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
                            <a
                                href="{{ \App\Filament\Resources\Appointments\AppointmentResource::getUrl('view', ['record' => $appointment]) }}"
                                class="agenda-event status-{{ $appointment->status }}"
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
                        @empty
                            <div class="agenda-empty">Sin citas</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <style>
        .agenda-shell { --agenda-ink: #17130f; --agenda-brass: #b7791f; --agenda-cream: #faf7f1; display: grid; gap: 1rem; }
        .agenda-toolbar { align-items: end; display: flex; gap: 1rem; justify-content: space-between; }
        .agenda-navigation { align-items: center; display: flex; flex-wrap: wrap; gap: .65rem; }
        .agenda-navigation strong { font-size: 1.05rem; margin-left: .35rem; }
        .agenda-icon-button, .agenda-today { align-items: center; background: white; border: 1px solid #d8d3ca; color: var(--agenda-ink); display: inline-flex; height: 2.5rem; justify-content: center; }
        .agenda-icon-button { width: 2.5rem; }
        .agenda-icon-button svg { height: 1.1rem; width: 1.1rem; }
        .agenda-today { font-size: .875rem; font-weight: 700; padding: 0 .9rem; }
        .agenda-filters { display: flex; gap: .75rem; }
        .agenda-filters label { color: #71685e; display: grid; font-size: .72rem; font-weight: 700; gap: .3rem; text-transform: uppercase; }
        .agenda-filters select { background: white; border: 1px solid #d8d3ca; color: var(--agenda-ink); font-size: .875rem; min-width: 10rem; padding: .62rem .75rem; text-transform: none; }
        .agenda-live { align-items: center; color: #71685e; display: flex; font-size: .78rem; gap: .45rem; justify-content: flex-end; }
        .agenda-live span { animation: agenda-pulse 1.8s infinite; background: #16a34a; border-radius: 50%; height: .5rem; width: .5rem; }
        .agenda-week { background: #d8d3ca; display: grid; gap: 1px; grid-template-columns: repeat(7, minmax(0, 1fr)); overflow-x: auto; }
        .agenda-day { background: var(--agenda-cream); min-height: 34rem; min-width: 10rem; }
        .agenda-day > header { align-items: baseline; border-bottom: 1px solid #e5e0d8; display: grid; grid-template-columns: 1fr auto; padding: .85rem; }
        .agenda-day > header span { color: #71685e; font-size: .72rem; font-weight: 800; text-transform: uppercase; }
        .agenda-day > header strong { font-size: 1.45rem; grid-row: span 2; }
        .agenda-day > header small { color: #9a9085; font-size: .7rem; }
        .agenda-day.is-today > header { background: var(--agenda-ink); color: white; }
        .agenda-day.is-today > header span, .agenda-day.is-today > header small { color: #d6b26e; }
        .agenda-events { display: grid; gap: .55rem; padding: .55rem; }
        .agenda-event { background: white; border-left: 3px solid var(--agenda-brass); box-shadow: 0 1px 2px rgb(23 19 15 / .08); color: var(--agenda-ink); display: grid; gap: .18rem; padding: .7rem; transition: transform .15s ease, box-shadow .15s ease; }
        .agenda-event:hover { box-shadow: 0 5px 14px rgb(23 19 15 / .12); transform: translateY(-1px); }
        .agenda-event.status-cancelled { border-color: #dc2626; opacity: .65; }
        .agenda-event.status-completed { border-color: #2563eb; }
        .agenda-event.status-pending { border-color: #d97706; }
        .agenda-event-top { align-items: center; display: flex; justify-content: space-between; }
        .agenda-event time { font-size: .95rem; font-weight: 800; }
        .agenda-event-top span { color: #71685e; font-size: .62rem; font-weight: 700; text-transform: uppercase; }
        .agenda-event > strong { font-size: .82rem; line-height: 1.25; margin-top: .2rem; }
        .agenda-event > small { color: #71685e; font-size: .7rem; line-height: 1.3; }
        .agenda-event .agenda-phone { color: var(--agenda-brass); font-weight: 700; margin-top: .2rem; }
        .agenda-empty { color: #aaa096; font-size: .75rem; padding: .65rem .3rem; text-align: center; }
        @keyframes agenda-pulse { 50% { opacity: .35; transform: scale(.82); } }
        @media (max-width: 900px) {
            .agenda-toolbar { align-items: stretch; flex-direction: column; }
            .agenda-filters { display: grid; grid-template-columns: 1fr 1fr; }
            .agenda-filters select { min-width: 0; width: 100%; }
            .agenda-week { background: transparent; display: grid; gap: .75rem; grid-template-columns: 1fr; overflow: visible; }
            .agenda-day { border: 1px solid #d8d3ca; min-height: 0; min-width: 0; }
            .agenda-events { grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr)); }
        }
        @media (prefers-reduced-motion: reduce) { .agenda-live span { animation: none; } .agenda-event { transition: none; } }
    </style>
</x-filament-panels::page>
