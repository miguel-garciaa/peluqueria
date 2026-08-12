<x-filament-panels::page>
    <div class="history-shell" wire:poll.30s="refreshHistory">
        <section class="history-toolbar" aria-labelledby="history-period-heading">
            <div class="history-period-copy">
                <h2 id="history-period-heading">{{ $this->periodLabel() }}</h2>
                <p>{{ $this->rangeLabel() }}</p>
                <small>Solo se muestran citas completadas. Las canceladas nunca entran en este historial.</small>
            </div>

            <div class="history-filters">
                {{ $this->filters }}
            </div>
        </section>

        @php($summary = $this->summary())
        <section class="history-summary" aria-label="Resumen del historial">
            <div>
                <span>Citas completadas</span>
                <strong>{{ number_format($summary['appointments'], 0, ',', '.') }}</strong>
            </div>
            <div>
                <span>Clientes atendidos</span>
                <strong>{{ number_format($summary['customers'], 0, ',', '.') }}</strong>
            </div>
            <div>
                <span>Servicios distintos</span>
                <strong>{{ number_format($summary['services'], 0, ',', '.') }}</strong>
            </div>
            <div>
                <span>Total cobrado</span>
                <strong>{{ number_format($summary['collected'], 2, ',', '.') }} €</strong>
            </div>
            <div>
                <span>Última finalización</span>
                <strong class="history-summary-date">{{ $summary['latest'] ?? 'Sin datos' }}</strong>
            </div>
        </section>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
