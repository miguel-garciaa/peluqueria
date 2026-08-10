<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\AppointmentRequest;
use Illuminate\Http\JsonResponse;

class AppointmentRequestController extends Controller
{
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $appointmentRequest = AppointmentRequest::create([
            'full_name' => $validated['fullName'],
            'phone' => $validated['phone'],
            'service_id' => $validated['serviceId'],
            'requested_date' => $validated['date'],
            'time_slot' => $validated['timeSlot'],
        ]);

        return response()->json([
            'message' => 'Solicitud recibida. Te contactaremos muy pronto.',
            'bookingId' => $appointmentRequest->getKey(),
        ], 201);
    }
}
