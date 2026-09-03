<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CompleteElapsedAppointments
{
    public function __construct(
        private readonly RecordCompletedAppointmentPayment $recordPayment,
    ) {}

    public function handle(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $completed = 0;

        Appointment::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('ends_at', '<=', $now->utc())
            ->orderBy('id')
            ->chunkById(100, function ($appointments) use ($now, &$completed): void {
                foreach ($appointments as $appointment) {
                    $didComplete = DB::transaction(function () use ($appointment, $now): bool {
                        $locked = Appointment::query()->lockForUpdate()->findOrFail($appointment->getKey());

                        if (! in_array($locked->status, ['pending', 'confirmed'], true)
                            || $locked->ends_at->isAfter($now->utc())) {
                            return false;
                        }

                        $locked->forceFill([
                            'status' => 'completed',
                            'completed_at' => $locked->ends_at,
                        ])->save();
                        $this->recordPayment->handle($locked);

                        return true;
                    }, 3);

                    $completed += (int) $didComplete;
                }
            });

        return $completed;
    }
}
