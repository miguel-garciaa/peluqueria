<?php

namespace App\Services;

use App\Models\Appointment;
use App\Support\AppointmentHistoryPeriod;
use Carbon\CarbonImmutable;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;

class AppointmentHistoryPdf
{
    /** @param Collection<int, Appointment> $appointments */
    public function render(
        Collection $appointments,
        AppointmentHistoryPeriod $period,
        CarbonImmutable $generatedAt,
        ?string $serviceName = null,
        ?string $professionalName = null,
    ): string {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->loadHtml(view('pdf.appointment-history', [
            'appointments' => $appointments,
            'period' => $period,
            'generatedAt' => $generatedAt,
            'serviceName' => $serviceName,
            'professionalName' => $professionalName,
            'uniqueCustomers' => $appointments->pluck('user_id')->filter()->unique()->count(),
            'totalMinutes' => $appointments->sum(
                fn ($appointment): int => (int) $appointment->starts_at->diffInMinutes($appointment->ends_at),
            ),
        ])->render());
        $dompdf->render();

        return $dompdf->output();
    }
}
