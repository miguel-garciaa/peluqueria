<?php

namespace Tests\Feature;

use App\Filament\Resources\Schedules\Pages\CreateSchedule;
use App\Filament\Resources\Schedules\Pages\EditSchedule;
use App\Filament\Resources\Schedules\Pages\ListSchedules;
use App\Filament\Resources\Schedules\ScheduleResource;
use App\Models\Professional;
use App\Models\Schedule;
use App\Models\User;
use App\Services\ManageScheduleGroup;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_weekly_schedule_can_be_created_for_several_days_as_one_group(): void
    {
        $professional = $this->professional();

        $schedule = app(ManageScheduleGroup::class)->create([
            'professional_id' => $professional->id,
            'days_of_week' => [1, 2, 3, 4, 5],
            'starts_at' => '09:30',
            'ends_at' => '20:00',
            'slot_interval_minutes' => 30,
            'is_active' => true,
        ]);

        $this->assertSame([1, 2, 3, 4, 5], $schedule->groupedDays());
        $this->assertSame('Lunes–Viernes', $schedule->days_label);
        $this->assertSame(5, Schedule::query()->where('group_id', $schedule->group_id)->count());
        $this->assertSame(1, ScheduleResource::getEloquentQuery()->count());
    }

    public function test_editing_a_weekly_group_updates_all_its_days_together(): void
    {
        $professional = $this->professional();
        $manager = app(ManageScheduleGroup::class);
        $schedule = $manager->create([
            'professional_id' => $professional->id,
            'days_of_week' => [1, 2, 3, 4, 5],
            'starts_at' => '09:30',
            'ends_at' => '20:00',
            'slot_interval_minutes' => 30,
            'is_active' => true,
        ]);

        $updated = $manager->update($schedule, [
            'professional_id' => $professional->id,
            'days_of_week' => [1, 3, 5],
            'starts_at' => '10:00',
            'ends_at' => '18:00',
            'slot_interval_minutes' => 60,
            'is_active' => true,
        ]);

        $rows = Schedule::query()->where('group_id', $schedule->group_id)->orderBy('day_of_week')->get();
        $this->assertSame([1, 3, 5], $updated->groupedDays());
        $this->assertCount(3, $rows);
        $this->assertTrue($rows->every(fn (Schedule $row): bool => substr((string) $row->starts_at, 0, 5) === '10:00'));
        $this->assertTrue($rows->every(fn (Schedule $row): bool => $row->slot_interval_minutes === 60));
    }

    public function test_overlapping_blocks_are_rejected_with_the_conflicting_days(): void
    {
        $professional = $this->professional();
        $manager = app(ManageScheduleGroup::class);
        $manager->create([
            'professional_id' => $professional->id,
            'days_of_week' => [1, 2, 3, 4, 5],
            'starts_at' => '09:30',
            'ends_at' => '14:00',
            'slot_interval_minutes' => 30,
            'is_active' => true,
        ]);

        try {
            $manager->create([
                'professional_id' => $professional->id,
                'days_of_week' => [3, 6],
                'starts_at' => '13:30',
                'ends_at' => '18:00',
                'slot_interval_minutes' => 30,
                'is_active' => true,
            ]);
            $this->fail('El bloque solapado debería haber sido rechazado.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Ya existe un horario que se solapa en: Miércoles.'],
                $exception->errors()['data.days_of_week'],
            );
        }

        $this->assertSame(5, Schedule::query()->count());
    }

    public function test_the_filament_form_creates_one_compact_weekly_group(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $professional = $this->professional();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateSchedule::class)
            ->fillForm([
                'professional_id' => $professional->id,
                'days_of_week' => ['1', '2', '3', '4'],
                'starts_at' => '09:30',
                'ends_at' => '20:00',
                'slot_interval_minutes' => 30,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(4, Schedule::query()->count());
        $this->assertSame(1, Schedule::query()->distinct()->count('group_id'));
    }

    public function test_filament_lists_and_edits_a_group_as_one_weekly_block(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $professional = $this->professional();
        $schedule = app(ManageScheduleGroup::class)->create([
            'professional_id' => $professional->id,
            'days_of_week' => [1, 2, 3, 4, 5],
            'starts_at' => '09:30',
            'ends_at' => '20:00',
            'slot_interval_minutes' => 30,
            'is_active' => true,
        ]);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ListSchedules::class)
            ->assertCountTableRecords(1)
            ->assertSee('Lunes–Viernes');

        Livewire::test(EditSchedule::class, ['record' => $schedule->getRouteKey()])
            ->assertFormSet(['days_of_week' => [1, 2, 3, 4, 5]])
            ->fillForm([
                'days_of_week' => ['2', '4', '6'],
                'starts_at' => '10:00',
                'ends_at' => '18:00',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([2, 4, 6], $schedule->refresh()->groupedDays());
        $this->assertSame(3, Schedule::query()->where('group_id', $schedule->group_id)->count());
    }

    private function professional(): Professional
    {
        return Professional::query()->create([
            'slug' => 'laura-horarios',
            'name' => 'Laura Horarios',
            'is_active' => true,
        ]);
    }
}
