<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Models\Schedule;
use App\Services\ManageScheduleGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    protected static ?string $title = 'Editar horario semanal';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->modalHeading('Borrar horario semanal')
                ->modalDescription('Se eliminarán todos los días incluidos en este bloque.')
                ->using(fn (Schedule $record): bool => app(ManageScheduleGroup::class)->delete($record)),
        ];
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['days_of_week'] = $this->record->groupedDays();

        return $data;
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Schedule $record */
        return app(ManageScheduleGroup::class)->update($record, $data);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Horario semanal actualizado';
    }
}
