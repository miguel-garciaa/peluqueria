<?php

namespace App\Console\Commands;

use App\Services\CompleteElapsedAppointments as CompleteElapsedAppointmentsService;
use Illuminate\Console\Command;

class CompleteElapsedAppointments extends Command
{
    protected $signature = 'appointments:complete';

    protected $description = 'Marca como completadas las citas cuya hora de finalización ya ha pasado';

    public function handle(CompleteElapsedAppointmentsService $appointments): int
    {
        $count = $appointments->handle();

        $this->components->info("Citas completadas: {$count}");

        return self::SUCCESS;
    }
}
