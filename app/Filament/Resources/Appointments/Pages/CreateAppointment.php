<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Services\ManageAppointment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(ManageAppointment::class)->prepare($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel());
        $record->forceFill($data)->save();

        return $record;
    }

    protected function afterCreate(): void
    {
        if ($this->record->status === 'cancelled') {
            app(ManageAppointment::class)->sendCancellation($this->record);

            return;
        }

        app(ManageAppointment::class)->sendConfirmation($this->record);
    }
}
