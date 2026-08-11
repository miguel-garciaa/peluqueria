<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointment;
use App\Services\BookAppointment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(StoreAppointment $request, BookAppointment $bookAppointment): JsonResponse
    {
        $appointment = $bookAppointment->handle($request->user(), $request->validated());

        return response()->json([
            'message' => 'Cita confirmada. Te hemos enviado los detalles por correo.',
            'appointment' => [
                'reference' => $appointment->reference,
                'service' => $appointment->service->name,
                'professional' => $appointment->professional->name,
                'startsAt' => $appointment->starts_at->toIso8601String(),
            ],
        ], 201);
    }

    public function index(Request $request): View
    {
        $appointments = $request->user()->appointments()
            ->with(['service', 'professional'])
            ->latest('starts_at')
            ->get()
            ->map(fn ($appointment) => [
                'reference' => $appointment->reference,
                'service' => $appointment->service->name,
                'professional' => $appointment->professional->name,
                'customDetails' => $appointment->custom_details,
                'startsAt' => $appointment->starts_at->toIso8601String(),
                'endsAt' => $appointment->ends_at->toIso8601String(),
                'status' => $appointment->status,
            ]);

        return view('appointments.index', [
            'currentUser' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone' => $request->user()->phone,
                'avatarUrl' => $request->user()->avatar_url,
            ],
            'appointments' => $appointments,
        ]);
    }
}
