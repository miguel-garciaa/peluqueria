<?php

namespace App\Services;

use App\Models\Appointment;
use App\Support\AppointmentHistoryPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class AppointmentHistoryReport
{
    public function query(
        AppointmentHistoryPeriod $period,
        CarbonImmutable $now,
        ?int $serviceId = null,
        ?int $professionalId = null,
    ): Builder {
        $start = $period->startsAt($now);

        return Appointment::query()
            ->where('status', 'completed')
            ->where('ends_at', '<=', $now->utc())
            ->when($start, fn (Builder $query): Builder => $query->where('ends_at', '>=', $start->utc()))
            ->when($serviceId, fn (Builder $query): Builder => $query->where('service_id', $serviceId))
            ->when($professionalId, fn (Builder $query): Builder => $query->where('professional_id', $professionalId));
    }
}
