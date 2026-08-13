<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\AppointmentPushNotifications;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-push-reminders';

    protected $description = 'Envía el recordatorio push de las citas que empiezan durante las próximas 24 horas';

    public function handle(AppointmentPushNotifications $pushNotifications): int
    {
        if (! $pushNotifications->configured()) {
            $this->components->warn('Web Push no está configurado; no se enviaron recordatorios.');

            return self::SUCCESS;
        }

        $sent = 0;
        Appointment::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNull('push_reminder_sent_at')
            ->where('starts_at', '>', now())
            ->where('starts_at', '<=', now()->addDay())
            ->whereHas('user.pushSubscriptions')
            ->with(['user.pushSubscriptions', 'service', 'professional'])
            ->orderBy('id')
            ->chunkById(100, function ($appointments) use ($pushNotifications, &$sent): void {
                foreach ($appointments as $appointment) {
                    $claimed = Appointment::query()
                        ->whereKey($appointment->getKey())
                        ->whereNull('push_reminder_sent_at')
                        ->update(['push_reminder_sent_at' => now()]);

                    if ($claimed !== 1) {
                        continue;
                    }

                    $pushNotifications->reminder($appointment);
                    $sent++;
                }
            });

        $this->components->info("{$sent} recordatorio(s) push encolado(s).");

        return self::SUCCESS;
    }
}
