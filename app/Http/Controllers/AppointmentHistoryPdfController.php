<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\Service;
use App\Services\AppointmentHistoryPdf;
use App\Services\AppointmentHistoryReport;
use App\Services\CompleteElapsedAppointments;
use App\Support\AppointmentHistoryPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppointmentHistoryPdfController extends Controller
{
    public function __invoke(
        Request $request,
        CompleteElapsedAppointments $completeElapsedAppointments,
        AppointmentHistoryReport $historyReport,
        AppointmentHistoryPdf $historyPdf,
    ): StreamedResponse {
        abort_unless($request->user()?->isPanelAdmin(), 403);

        $data = $request->validate([
            'period' => ['required', Rule::enum(AppointmentHistoryPeriod::class)],
            'service' => ['nullable', 'integer', 'exists:services,id'],
            'professional' => ['nullable', 'integer', 'exists:professionals,id'],
        ]);

        $completeElapsedAppointments->handle();

        $now = CarbonImmutable::now(config('app.business_timezone'));
        $period = AppointmentHistoryPeriod::fromValue($data['period']);
        $serviceId = isset($data['service']) ? (int) $data['service'] : null;
        $professionalId = isset($data['professional']) ? (int) $data['professional'] : null;
        $service = $serviceId ? Service::query()->find($serviceId) : null;
        $professional = $professionalId ? Professional::query()->find($professionalId) : null;
        $appointments = $historyReport
            ->query($period, $now, $serviceId, $professionalId)
            ->with(['user:id,email', 'service:id,name', 'professional:id,name', 'payment:id,booking_id,method,status,amount,paid_at'])
            ->latest('ends_at')
            ->get();
        $pdf = $historyPdf->render(
            $appointments,
            $period,
            $now,
            $service?->name,
            $professional?->name,
        );
        $filename = 'historial-citas-'.$period->fileLabel().'-'.$now->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            static fn () => print $pdf,
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
