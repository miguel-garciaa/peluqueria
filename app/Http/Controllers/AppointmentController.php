<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointment;
use App\Models\Appointment;
use App\Services\BookAppointment;
use App\Services\BookingCatalog;
use App\Services\CompleteElapsedAppointments;
use App\Services\ManageAppointment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(StoreAppointment $request, BookAppointment $bookAppointment): JsonResponse
    {
        $appointment = $bookAppointment->handle($request->user(), $request->validated());

        return response()->json([
            'message' => 'Cita confirmada. Recibirás los detalles por correo.',
            'appointment' => [
                'reference' => $appointment->reference,
                'service' => $appointment->service->name,
                'professional' => $appointment->professional->name,
                'startsAt' => $appointment->starts_at->toIso8601String(),
            ],
        ], 201);
    }

    public function index(
        Request $request,
        BookingCatalog $bookingCatalog,
        CompleteElapsedAppointments $completeElapsedAppointments,
    ): View {
        $completeElapsedAppointments->handle();

        $appointments = $request->user()->appointments()
            ->where('status', '!=', 'cancelled')
            ->select([
                'id', 'reference', 'user_id', 'service_id', 'professional_id', 'custom_details',
                'starts_at', 'ends_at', 'status',
            ])
            ->with([
                'service:id,name',
                'professional:id,name',
            ])
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
                'canCancel' => $appointment->canBeCancelled(),
                'cancelUrl' => route('appointments.cancel', $appointment->reference, false),
            ]);

        return view('appointments.index', [
            'currentUser' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone' => $request->user()->phone,
                'avatarUrl' => $request->user()->avatar_url,
                'isAdmin' => $request->user()->isPanelAdmin(),
            ],
            'appointments' => $appointments,
            'bookingCatalog' => $bookingCatalog->get(),
        ]);
    }

    public function cancel(Request $request, string $reference, ManageAppointment $manageAppointment): RedirectResponse
    {
        /** @var Appointment $appointment */
        $appointment = $request->user()->appointments()
            ->where('reference', $reference)
            ->firstOrFail();

        if (! $manageAppointment->cancel($appointment)) {
            return to_route('appointments.index')
                ->with('appointment_error', 'Esta cita ya no se puede anular.');
        }

        return to_route('appointments.index')
            ->with('appointment_status', 'La cita se ha anulado correctamente. Recibirás la confirmación por correo.');
    }
}
