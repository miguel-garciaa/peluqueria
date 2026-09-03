<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cita anulada</title>
</head>
<body style="margin:0;padding:0;background:#f7f4ed;color:#211f1c;font-family:Arial,sans-serif">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse;background:#f7f4ed">
        <tr>
            <td align="center" style="padding:32px 18px">
                <!--[if mso]><table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:620px;border:1px solid #ded8cc;border-collapse:collapse;background:#ffffff">
                    <tr>
                        <td style="background:#211f1c;padding:28px;color:#ffffff">
                            <p style="margin:0 0 8px;color:#d4ad65;font-size:13px;font-weight:700;line-height:1.4">{{ mb_strtoupper(config('app.name')) }} · PELUQUERÍA Y BARBERÍA</p>
                            <h1 style="margin:0;color:#ffffff;font-size:30px;line-height:1.1">Tu cita ha sido anulada</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;padding:28px">
                            <p style="margin:0 0 10px;font-size:16px;line-height:1.6">Hola {{ $appointment->customer_name }}, hemos registrado correctamente la anulación de tu cita.</p>
                            <p style="margin:0 0 24px;color:#746e65;font-size:16px;line-height:1.6">El horario ha quedado liberado y no necesitas realizar ninguna otra gestión.</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse">
                                <tr><td width="42%" style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Servicio</td><td width="58%" align="right" style="padding:12px 0;border-bottom:1px solid #eee8dd;font-weight:700;word-break:break-word">{{ $appointment->service->name }}</td></tr>
                                <tr><td width="42%" style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Profesional</td><td width="58%" align="right" style="padding:12px 0;border-bottom:1px solid #eee8dd;font-weight:700;word-break:break-word">{{ $appointment->professional->name }}</td></tr>
                                <tr><td width="42%" style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Fecha anulada</td><td width="58%" align="right" style="padding:12px 0;border-bottom:1px solid #eee8dd;font-weight:700;word-break:break-word">{{ $appointment->starts_at->timezone(config('app.business_timezone'))->translatedFormat('l, j \d\e F') }}</td></tr>
                                <tr><td width="42%" style="padding:12px 0;border-bottom:1px solid #eee8dd;color:#746e65">Hora</td><td width="58%" align="right" style="padding:12px 0;border-bottom:1px solid #eee8dd;font-weight:700">{{ $appointment->starts_at->timezone(config('app.business_timezone'))->format('H:i') }}</td></tr>
                                <tr><td width="42%" style="padding:12px 0;color:#746e65">Referencia</td><td width="58%" align="right" style="padding:12px 0;font-size:12px;font-weight:700;word-break:break-all">{{ $appointment->reference }}</td></tr>
                            </table>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:26px;border-collapse:collapse">
                                <tr><td style="background:#211f1c"><a href="{{ url('/reservar') }}" style="display:inline-block;padding:13px 20px;color:#ffffff;font-weight:700;text-decoration:none">Reservar otra cita</a></td></tr>
                            </table>
                            <p style="margin:26px 0 0;color:#746e65;font-size:14px;line-height:1.6">Calle Principal, 00 · Tu ciudad<br>600 00 00 00</p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
