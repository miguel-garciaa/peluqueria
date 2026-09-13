<?php

namespace Tests\Concerns;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

trait CreatesBookingData
{
    /** @return array{User, Service, Professional} */
    protected function bookingCatalog(array $serviceOverrides = [], array $professionalOverrides = []): array
    {
        $user = User::factory()->create();
        $service = Service::query()->create(array_merge([
            'slug' => 'test-service',
            'name' => 'Servicio de prueba',
            'duration_minutes' => 60,
            'price_from' => 35,
            'is_custom' => false,
            'is_active' => true,
        ], $serviceOverrides));
        $professional = Professional::query()->create(array_merge([
            'slug' => 'test-professional',
            'name' => 'Profesional de prueba',
            'role' => 'Estilista',
            'is_active' => true,
        ], $professionalOverrides));
        $professional->services()->attach($service);

        return [$user, $service, $professional];
    }

    protected function schedule(
        Professional $professional,
        int $dayOfWeek = 1,
        string $startsAt = '09:00',
        string $endsAt = '14:00',
        int $interval = 30,
        bool $active = true,
    ): Schedule {
        return Schedule::query()->create([
            'professional_id' => $professional->id,
            'day_of_week' => $dayOfWeek,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'slot_interval_minutes' => $interval,
            'is_active' => $active,
        ]);
    }

    protected function appointment(
        User $user,
        Service $service,
        Professional $professional,
        CarbonImmutable $startsAt,
        string $status = 'confirmed',
        array $overrides = [],
    ): Appointment {
        return Appointment::query()->forceCreate(array_merge([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $user->name,
            'customer_phone' => '+34 600 11 22 33',
            'starts_at' => $startsAt->utc(),
            'ends_at' => $startsAt->addMinutes($service->duration_minutes)->utc(),
            'status' => $status,
            'payment_method' => 'cash',
            'payment_amount' => $service->price_from,
        ], $overrides));
    }
}
