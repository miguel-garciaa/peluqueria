<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CompleteElapsedAppointments
{
    public function handle(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        return Appointment::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('ends_at', '<=', $now->utc())
            ->update([
                'status' => 'completed',
                'completed_at' => DB::raw('ends_at'),
                'updated_at' => $now->utc(),
            ]);
    }
}
