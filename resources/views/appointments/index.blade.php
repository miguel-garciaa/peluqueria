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
    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=4">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <title>Mis citas — {{ config('app.name') }}</title>
    @viteReactRefresh
    @vite('resources/js/appointments.tsx')
</head>
<body>
    <div
        id="appointments-root"
        data-current-user="{{ json_encode($currentUser) }}"
        data-appointments="{{ json_encode($appointments) }}"
        data-booking-endpoint="{{ route('bookings.store', absolute: false) }}"
        data-availability-endpoint="{{ route('bookings.availability', absolute: false) }}"
        data-booking-catalog="{{ json_encode($bookingCatalog) }}"
        data-flash="{{ json_encode([
            'message' => session('appointment_status') ?? session('appointment_error'),
            'type' => session()->has('appointment_error') ? 'error' : 'success',
        ]) }}"
        data-push-public-key="{{ config('webpush.vapid.public_key') }}"
        data-push-subscription-endpoint="{{ route('push-subscriptions.store', absolute: false) }}"
    ></div>
</body>
</html>
