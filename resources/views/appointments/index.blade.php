<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#171512">
    <title>Mis citas — Baskuñana Peluqueros</title>
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
    ></div>
</body>
</html>
