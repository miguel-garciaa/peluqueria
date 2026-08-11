<?php

namespace Database\Seeders;

use App\Models\Professional;
use App\Models\Schedule;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $services = collect([
            ['slug' => 'cut', 'name' => 'Corte & Peinado', 'duration_minutes' => 45, 'price_from' => 35],
            ['slug' => 'balayage', 'name' => 'Balayage', 'duration_minutes' => 150, 'price_from' => 90],
            ['slug' => 'keratin', 'name' => 'Keratina & Brillo', 'duration_minutes' => 120, 'price_from' => 75],
            ['slug' => 'barber', 'name' => 'Barba & Estilo', 'duration_minutes' => 30, 'price_from' => 22],
            ['slug' => 'ritual', 'name' => 'Ritual Capilar', 'duration_minutes' => 60, 'price_from' => 48],
            ['slug' => 'event', 'name' => 'Peinado de Evento', 'duration_minutes' => 75, 'price_from' => 55],
            ['slug' => 'custom', 'name' => 'Personalizado', 'duration_minutes' => 60, 'price_from' => null, 'is_custom' => true],
        ])->map(fn (array $service) => Service::query()->updateOrCreate(
            ['slug' => $service['slug']],
            [...$service, 'is_active' => true],
        ));

        collect([
            ['slug' => 'laura', 'name' => 'Laura Baskuñana', 'role' => 'Directora creativa'],
            ['slug' => 'dani', 'name' => 'Dani Ros', 'role' => 'Barbero y estilista'],
            ['slug' => 'marta', 'name' => 'Marta Soler', 'role' => 'Especialista en color'],
            ['slug' => 'alvaro', 'name' => 'Álvaro León', 'role' => 'Estilista y terapeuta capilar'],
        ])->each(function (array $data) use ($services): void {
            $professional = Professional::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [...$data, 'is_active' => true],
            );
            $professional->services()->sync($services->pluck('id'));

            foreach (range(1, 5) as $day) {
                Schedule::query()->updateOrCreate(
                    ['professional_id' => $professional->id, 'day_of_week' => $day, 'starts_at' => '09:30'],
                    ['ends_at' => '20:00', 'slot_interval_minutes' => 30, 'is_active' => true],
                );
            }

            Schedule::query()->updateOrCreate(
                ['professional_id' => $professional->id, 'day_of_week' => 6, 'starts_at' => '09:00'],
                ['ends_at' => '15:00', 'slot_interval_minutes' => 30, 'is_active' => true],
            );
        });
    }
}
