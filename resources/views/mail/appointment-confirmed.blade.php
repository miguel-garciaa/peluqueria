<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cita confirmada</title>
</head>
<body style="margin:0;padding:0;background:#f7f4ed;color:#211f1c;font-family:Arial,sans-serif">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse;background:#f7f4ed">
        <tr>
            <td align="center" style="padding:32px 18px">
                <!--[if mso]><table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:620px;border:1px solid #ded8cc;border-collapse:collapse;background:#ffffff">
                    <tr>
                        <td style="background:#211f1c;padding:28px;color:#ffffff">
                            <p style="margin:0 0 8px;color:#d4ad65;font-size:13px;font-weight:700;line-height:1.4">BASKUÑANA PELUQUEROS · CARTAGENA</p>
                            <h1 style="margin:0;color:#ffffff;font-size:30px;line-height:1.1">Tu cita está confirmada</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;padding:28px">
                            <p style="margin:0 0 24px;font-size:16px;line-height:1.6">Hola {{ $appointment->customer_name }}, ya hemos reservado tu momento con nosotros.</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse">
                                <tr><td width="42%" style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Servicio</td><td width="58%" align="right" style="padding:12px 0;border-bottom:1px solid #eee8dd;font-weight:700;word-break:break-word">{{ $appointment->service->name }}</td></tr>
                                <tr><td width="42%" style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Profesional</td><td width="58%" align="right" style="padding:12px 0;border-bottom:1px solid #eee8dd;font-weight:700;word-break:break-word">{{ $appointment->professional->name }}</td></tr>
                                <tr><td width="42%" style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Fecha</td><td width="58%" align="right" style="padding:12px 0;border-bottom:1px solid #eee8dd;font-weight:700;word-break:break-word">{{ $appointment->starts_at->timezone(config('app.business_timezone'))->translatedFormat('l, j \d\e F') }}</td></tr>
                                <tr><td width="42%" style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Hora</td><td width="58%" align="right" style="padding:12px 0;border-bottom:1px solid #eee8dd;font-weight:700">{{ $appointment->starts_at->timezone(config('app.business_timezone'))->format('H:i') }}</td></tr>
                                <tr><td width="42%" style="padding:12px 0;color:#746e65">Referencia</td><td width="58%" align="right" style="padding:12px 0;font-size:12px;font-weight:700;word-break:break-all">{{ $appointment->reference }}</td></tr>
                            </table>
                            @if ($appointment->custom_details)
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin-top:20px;border-collapse:collapse;background:#f3eee4">
                                    <tr><td style="padding:16px;line-height:1.5"><strong>Tu petición</strong><br>{{ $appointment->custom_details }}</td></tr>
                                </table>
                            @endif
                            <p style="margin:26px 0 0;color:#746e65;font-size:14px;line-height:1.6">Paseo Alfonso XIII, 28 · Cartagena<br>968 12 44 45</p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
