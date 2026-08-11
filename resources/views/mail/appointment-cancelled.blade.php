<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cita anulada</title>
</head>
<body style="margin:0;background:#f7f4ed;color:#211f1c;font-family:Arial,sans-serif">
    <div style="max-width:620px;margin:0 auto;padding:32px 18px">
        <div style="background:#211f1c;padding:28px;color:#fff">
            <p style="margin:0 0 8px;color:#d4ad65;font-size:13px;font-weight:700">BASKUÑANA PELUQUEROS · CARTAGENA</p>
            <h1 style="margin:0;font-size:30px;line-height:1.1">Tu cita ha sido anulada</h1>
        </div>
        <div style="border:1px solid #ded8cc;border-top:0;background:#fff;padding:28px">
            <p style="margin:0 0 10px;line-height:1.6">Hola {{ $appointment->customer_name }}, hemos registrado correctamente la anulación de tu cita.</p>
            <p style="margin:0 0 24px;color:#746e65;line-height:1.6">El horario ha quedado liberado y no necesitas realizar ninguna otra gestión.</p>
            <table role="presentation" style="width:100%;border-collapse:collapse">
                <tr><td style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Servicio</td><td style="padding:12px 0;border-bottom:1px solid #eee8dd;text-align:right;font-weight:700">{{ $appointment->service->name }}</td></tr>
                <tr><td style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Profesional</td><td style="padding:12px 0;border-bottom:1px solid #eee8dd;text-align:right;font-weight:700">{{ $appointment->professional->name }}</td></tr>
                <tr><td style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Fecha anulada</td><td style="padding:12px 0;border-bottom:1px solid #eee8dd;text-align:right;font-weight:700">{{ $appointment->starts_at->timezone(config('app.business_timezone'))->translatedFormat('l, j \d\e F') }}</td></tr>
                <tr><td style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Hora</td><td style="padding:12px 0;border-bottom:1px solid #eee8dd;text-align:right;font-weight:700">{{ $appointment->starts_at->timezone(config('app.business_timezone'))->format('H:i') }}</td></tr>
                <tr><td style="padding:12px 0;color:#746e65">Referencia</td><td style="padding:12px 0;text-align:right;font-weight:700">{{ $appointment->reference }}</td></tr>
            </table>
            <div style="margin-top:26px">
                <a href="{{ url('/#reservas') }}" style="display:inline-block;border-radius:999px;background:#211f1c;padding:13px 20px;color:#fff;font-weight:700;text-decoration:none">Reservar otra cita</a>
            </div>
            <p style="margin:26px 0 0;color:#746e65;font-size:14px;line-height:1.6">Paseo Alfonso XIII, 28 · Cartagena<br>968 12 44 45</p>
        </div>
    </div>
</body>
</html>
