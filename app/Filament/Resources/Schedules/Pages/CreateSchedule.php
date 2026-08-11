<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Services\ManageScheduleGroup;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    protected static ?string $title = 'Crear horario semanal';

    /** @param array<string, mixed> $data */
    protected function handleRecordCreation(array $data): Model
    {
        return app(ManageScheduleGroup::class)->create($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Horario semanal creado';
    }
}
