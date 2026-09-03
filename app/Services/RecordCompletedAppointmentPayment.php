<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;

class RecordCompletedAppointmentPayment
{
    public function handle(Appointment $appointment): ?Payment
    {
        if ($appointment->status !== 'completed' || $appointment->payment_method !== 'cash') {
            return null;
        }

        return $appointment->payment()->updateOrCreate([], [
            'method' => 'cash',
            'status' => 'paid',
            'amount' => $appointment->payment_amount,
            'currency' => config('payments.currency', 'EUR'),
            'paid_at' => $appointment->completed_at ?? $appointment->ends_at,
            'gateway_provider' => null,
            'gateway_reference' => null,
        ]);
    }
}
