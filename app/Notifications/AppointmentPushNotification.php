<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AppointmentPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const CONFIRMED = 'confirmed';

    public const UPDATED = 'updated';

    public const CANCELLED = 'cancelled';

    public const REMINDER = 'reminder';

    public const ADMIN_CREATED = 'admin_created';

    public const ADMIN_CANCELLED = 'admin_cancelled';

    public function __construct(
        public Appointment $appointment,
        public string $event,
    ) {
        $this->onConnection(config('queue.default'))
            ->onQueue('default')
            ->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $this->appointment->loadMissing(['service', 'professional']);
        [$title, $body] = $this->copy();
        $adminEvent = in_array($this->event, [self::ADMIN_CREATED, self::ADMIN_CANCELLED], true);

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->icon('/icons/icon-192.png')
            ->badge('/icons/icon-192.png')
            ->lang('es')
            ->tag('appointment-'.$this->appointment->reference)
            ->renotify()
            ->requireInteraction($adminEvent)
            ->vibrate([180, 80, 180])
            ->action('Ver cita', 'view_appointment')
            ->data([
                'url' => $adminEvent
                    ? '/admin/appointments/'.$this->appointment->getKey()
                    : '/mis-citas',
                'reference' => $this->appointment->reference,
            ])
            ->options([
                'TTL' => $this->event === self::REMINDER ? 21600 : 86400,
                'urgency' => $this->event === self::REMINDER ? 'normal' : 'high',
                'topic' => 'appointment-'.substr((string) $this->appointment->reference, -20),
            ]);
    }

    /** @return array{string, string} */
    private function copy(): array
    {
        $startsAt = $this->appointment->starts_at->timezone(config('app.business_timezone'));
        $date = $startsAt->locale('es')->translatedFormat('l j \d\e F \a \l\a\s H:i');
        $service = $this->appointment->service->name;
        $professional = $this->appointment->professional->name;

        return match ($this->event) {
            self::UPDATED => ['Tu cita se ha actualizado', "{$service}, {$date}, con {$professional}."],
            self::CANCELLED => ['Cita anulada', "Se ha anulado tu cita de {$service} del {$date}."],
            self::REMINDER => [$startsAt->isToday() ? 'Tu cita es hoy' : 'Tu cita es mañana', "{$service} a las {$startsAt->format('H:i')} con {$professional}."],
            self::ADMIN_CREATED => ['Nueva cita: '.$this->appointment->customer_name, "{$service}, {$date}, con {$professional}."],
            self::ADMIN_CANCELLED => ['Cita anulada: '.$this->appointment->customer_name, "{$service} del {$date}."],
            default => ['Cita confirmada', "{$service}, {$date}, con {$professional}."],
        };
    }
}
