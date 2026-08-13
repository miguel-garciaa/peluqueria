<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#171512">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.short_name') }}">
    <meta
        name="description"
        content="Peluquería y barbería en tu ciudad: corte, color, barba, tratamientos y asesoramiento capilar personalizado."
    >

    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=4">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">

    <title>Peluquería y barbería — Tu ciudad</title>

    @viteReactRefresh
    @vite('resources/js/main.tsx')
</head>
<body>
    <div
        id="root"
        data-booking-endpoint="{{ request()->getBaseUrl() }}/reservas"
        data-availability-endpoint="{{ request()->getBaseUrl() }}/reservas/disponibilidad"
        data-booking-catalog="{{ json_encode($bookingCatalog) }}"
        data-current-user="{{ json_encode($currentUser) }}"
        data-mobile-view="{{ $mobileView }}"
        data-auth-message="{{ $authMessage }}"
        data-auth-message-type="{{ $authMessageType }}"
    ></div>
</body>
</html>
