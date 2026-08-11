<?php

namespace App\Filament\Actions;

use App\Models\Appointment;
use App\Services\ManageAppointment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelAppointmentAction
{
    public static function make(string $name = 'cancelAppointment'): Action
    {
        return Action::make($name)
            ->label('Anular cita')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->link()
            ->requiresConfirmation()
            ->modalHeading('Anular cita')
            ->modalDescription('La cita se marcará como cancelada, el horario volverá a estar disponible y el cliente recibirá un correo de confirmación.')
            ->modalSubmitActionLabel('Sí, anular cita')
            ->visible(fn (?Appointment $record): bool => $record?->canBeCancelled() ?? true)
            ->action(function (?Appointment $record, array $arguments): void {
                $appointment = $record ?? Appointment::query()->findOrFail($arguments['appointment']);

                if (! app(ManageAppointment::class)->cancel($appointment)) {
                    Notification::make()
                        ->title('Esta cita ya no se puede anular')
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Cita anulada')
                    ->body('El horario está disponible de nuevo y el correo se ha enviado a la cola.')
                    ->success()
                    ->send();
            });
    }
}
