<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de citas completadas</title>
    <style>
        @page { margin: 22mm 13mm 16mm; }
        * { box-sizing: border-box; }
        body { color: #181510; font-family: "DejaVu Sans", sans-serif; font-size: 9px; margin: 0; }
        header { border-bottom: 2px solid #b7791f; margin-bottom: 15px; padding-bottom: 10px; }
        .brand { color: #181510; font-size: 18px; font-weight: bold; }
        h1 { font-size: 24px; line-height: 1.15; margin: 5px 0 3px; }
        .meta { color: #665d54; font-size: 9px; }
        .filters { margin-top: 5px; }
        .filter { background: #f3f0ea; border-radius: 10px; display: inline-block; margin-right: 5px; padding: 4px 8px; }
        .summary { margin: 0 0 14px; width: 100%; }
        .summary td { background: #f3f0ea; border-right: 5px solid #fff; padding: 8px 10px; width: 25%; }
        .summary td:last-child { border-right: 0; }
        .summary span { color: #70675d; display: block; font-size: 8px; margin-bottom: 2px; }
        .summary strong { font-size: 15px; }
        table.history { border-collapse: collapse; table-layout: fixed; width: 100%; }
        table.history thead { display: table-header-group; }
        table.history tr { page-break-inside: avoid; }
        table.history th { background: #191612; color: #fffdfa; font-size: 8px; padding: 7px 6px; text-align: left; }
        table.history td { border-bottom: 1px solid #ded8ce; padding: 7px 6px; vertical-align: top; word-wrap: break-word; }
        table.history tbody tr:nth-child(even) { background: #faf8f4; }
        .empty { border: 1px solid #d8d0c5; color: #70675d; padding: 28px; text-align: center; }
        .footer { bottom: -11mm; color: #70675d; font-size: 8px; left: 0; position: fixed; right: 0; text-align: center; }
        .page-number:after { content: counter(page); }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <footer class="footer">
        Baskuñana Peluqueros - Documento generado el {{ $generatedAt->locale('es')->translatedFormat('d/m/Y H:i') }} - Página <span class="page-number"></span>
    </footer>

    <header>
        <div class="brand">Baskuñana Peluqueros</div>
        <h1>Historial de citas completadas</h1>
        <div class="meta">{{ $period->label() }} · {{ $period->rangeLabel($generatedAt) }}</div>
        @if ($serviceName || $professionalName)
            <div class="filters">
                @if ($serviceName)<span class="filter">Servicio: {{ $serviceName }}</span>@endif
                @if ($professionalName)<span class="filter">Profesional: {{ $professionalName }}</span>@endif
            </div>
        @endif
    </header>

    <table class="summary">
        <tr>
            <td><span>Citas completadas</span><strong>{{ number_format($appointments->count(), 0, ',', '.') }}</strong></td>
            <td><span>Clientes atendidos</span><strong>{{ number_format($uniqueCustomers, 0, ',', '.') }}</strong></td>
            <td><span>Tiempo atendido</span><strong>{{ number_format($totalMinutes / 60, 1, ',', '.') }} h</strong></td>
            <td><span>Total cobrado</span><strong>{{ number_format($totalCollected, 2, ',', '.') }} €</strong></td>
        </tr>
    </table>

    @if ($appointments->isEmpty())
        <div class="empty">No hay citas completadas para los filtros y el periodo seleccionados.</div>
    @else
        <table class="history">
            <thead>
                <tr>
                    <th style="width: 12%">Fecha y hora</th>
                    <th style="width: 15%">Cliente</th>
                    <th style="width: 11%">Teléfono</th>
                    <th style="width: 16%">Servicio</th>
                    <th style="width: 14%">Profesional</th>
                    <th style="width: 7%">Duración</th>
                    <th style="width: 14%">Pago</th>
                    <th style="width: 11%">Referencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($appointments as $appointment)
                    <tr>
                        <td class="nowrap">{{ $appointment->starts_at->timezone(config('app.business_timezone'))->format('d/m/Y H:i') }}</td>
                        <td>{{ $appointment->customer_name }}<br><span class="meta">{{ $appointment->user?->email }}</span></td>
                        <td class="nowrap">{{ $appointment->customer_phone }}</td>
                        <td>{{ $appointment->service?->name }}</td>
                        <td>{{ $appointment->professional?->name }}</td>
                        <td class="nowrap">{{ (int) $appointment->starts_at->diffInMinutes($appointment->ends_at) }} min</td>
                        <td>
                            {{ \App\Models\Payment::methodOptions()[$appointment->payment_method] ?? 'Sin indicar' }}
                            <br>
                            <span class="meta">
                                {{ \App\Models\Payment::statusOptions()[$appointment->payment?->status] ?? 'Pendiente' }}
                                @if (($appointment->payment?->amount ?? $appointment->payment_amount) !== null)
                                    · {{ number_format((float) ($appointment->payment?->amount ?? $appointment->payment_amount), 2, ',', '.') }} €
                                @endif
                            </span>
                        </td>
                        <td>{{ $appointment->reference }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
