<?php

namespace App\Services;

use App\Models\Professional;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

class BookingCatalog
{
    /**
     * @return array{
     *     services: array<int, array{id: string, name: string, description: string|null, durationMinutes: int, priceFrom: float|null, isCustom: bool}>,
     *     professionals: array<int, array{id: string, name: string, role: string|null, serviceIds: array<int, string>}>
     * }
     */
    public function get(): array
    {
        $services = Service::query()
            ->active()
            ->select(['id', 'slug', 'name', 'description', 'duration_minutes', 'price_from', 'is_custom'])
            ->orderBy('id')
            ->get()
            ->map(fn (Service $service): array => [
                'id' => $service->slug,
                'name' => $service->name,
                'description' => $service->description,
                'durationMinutes' => $service->duration_minutes,
                'priceFrom' => $service->price_from !== null ? (float) $service->price_from : null,
                'isCustom' => $service->is_custom,
            ])
            ->values()
            ->all();

        $professionals = Professional::query()
            ->active()
            ->select(['id', 'slug', 'name', 'role'])
            ->whereHas('services', fn (Builder $query) => $query->where('services.is_active', true))
            ->with(['services' => fn ($query) => $query
                ->select(['services.id', 'services.slug'])
                ->active()
                ->orderBy('services.id')])
            ->orderBy('id')
            ->get()
            ->map(fn (Professional $professional): array => [
                'id' => $professional->slug,
                'name' => $professional->name,
                'role' => $professional->role,
                'serviceIds' => $professional->services->pluck('slug')->values()->all(),
            ])
            ->values()
            ->all();

        return compact('services', 'professionals');
    }
}
