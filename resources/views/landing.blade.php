<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#171512">
    <meta
        name="description"
        content="Baskuñana Peluqueros en Cartagena: corte, color, tratamientos y asesoramiento capilar personalizado."
    >

    <link rel="icon" href="/favicon.ico?v=3" sizes="any">
    <link rel="icon" type="image/png" href="/favicon.png?v=3">
    <link rel="apple-touch-icon" href="/favicon.png?v=3">

    <title>Baskuñana Peluqueros — Cartagena</title>

    @viteReactRefresh
    @vite('resources/js/main.tsx')
</head>
<body>
    <div
        id="root"
        data-booking-endpoint="{{ route('bookings.store', [], false) }}"
        data-availability-endpoint="{{ route('bookings.availability', [], false) }}"
        data-booking-catalog="{{ json_encode($bookingCatalog) }}"
        data-current-user="{{ json_encode($currentUser) }}"
        data-auth-message="{{ $authMessage }}"
        data-auth-message-type="{{ $authMessageType }}"
    ></div>
</body>
</html>
