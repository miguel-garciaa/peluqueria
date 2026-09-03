<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class ConfirmBizumPayment
{
    public function handle(
        Appointment $appointment,
        string $gatewayReference,
        ?float $amount = null,
        ?CarbonImmutable $paidAt = null,
        array $metadata = [],
    ): Payment {
        return DB::transaction(function () use ($appointment, $gatewayReference, $amount, $paidAt, $metadata): Payment {
            $locked = Appointment::query()->lockForUpdate()->findOrFail($appointment->getKey());

            if ($locked->payment_method !== 'bizum') {
                throw new DomainException('La cita no está configurada para pagar por Bizum.');
            }

            return $locked->payment()->updateOrCreate([], [
                'method' => 'bizum',
                'status' => 'paid',
                'amount' => $amount ?? $locked->payment_amount,
                'currency' => config('payments.currency', 'EUR'),
                'paid_at' => $paidAt ?? CarbonImmutable::now(),
                'gateway_provider' => 'redsys',
                'gateway_reference' => $gatewayReference,
                'metadata' => $metadata ?: null,
            ]);
        }, 3);
    }
}
