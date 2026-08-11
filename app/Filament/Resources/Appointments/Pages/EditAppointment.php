<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Services\ManageAppointment;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    private ?string $originalStatus = null;

    private ?string $originalSchedule = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $startsAt = $this->record->starts_at->timezone(config('app.business_timezone'));
        $data['appointment_date'] = $startsAt->format('Y-m-d');
        $data['appointment_time'] = $startsAt->format('H:i');

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->originalStatus = (string) $this->record->getOriginal('status');
        $this->originalSchedule = implode('|', [
            $this->record->getOriginal('service_id'),
            $this->record->getOriginal('professional_id'),
            $this->record->getRawOriginal('starts_at'),
        ]);
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(ManageAppointment::class)->prepare($data, $this->record);
    }

    protected function afterSave(): void
    {
        if ($this->record->status === 'cancelled' && $this->originalStatus !== 'cancelled') {
            app(ManageAppointment::class)->sendCancellation($this->record);

            return;
        }

        $currentSchedule = implode('|', [
            $this->record->service_id,
            $this->record->professional_id,
            $this->record->getRawOriginal('starts_at'),
        ]);

        if (in_array($this->record->status, ['pending', 'confirmed'], true)
            && ($this->originalStatus === 'cancelled' || $this->originalSchedule !== $currentSchedule)) {
            app(ManageAppointment::class)->sendConfirmation($this->record);
        }
    }
}
