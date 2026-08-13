<?php

namespace App\Notifications;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class AdminAppointmentDatabaseNotification extends Notification
{
    public function __construct(
        public Appointment $appointment,
        public string $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $this->appointment->loadMissing(['service', 'professional']);
        $startsAt = $this->appointment->starts_at->timezone(config('app.business_timezone'));
        $cancelled = $this->event === AppointmentPushNotification::ADMIN_CANCELLED;

        return FilamentNotification::make()
            ->title($cancelled ? 'Cita anulada' : 'Nueva cita reservada')
            ->body(sprintf(
                '%s · %s · %s con %s',
                $this->appointment->customer_name,
                $this->appointment->service->name,
                $startsAt->format('d/m/Y H:i'),
                $this->appointment->professional->name,
            ))
            ->status($cancelled ? 'danger' : 'success')
            ->icon($cancelled ? 'heroicon-o-x-circle' : 'heroicon-o-calendar-days')
            ->actions([
                Action::make('view')
                    ->label('Ver cita')
                    ->button()
                    ->url('/admin/appointments/'.$this->appointment->getKey())
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
