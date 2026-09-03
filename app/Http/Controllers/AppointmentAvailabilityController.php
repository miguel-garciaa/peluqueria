<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityRequest;
use App\Models\Professional;
use App\Models\Service;
use App\Services\AppointmentAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class AppointmentAvailabilityController extends Controller
{
    public function __invoke(AvailabilityRequest $request, AppointmentAvailability $availability): JsonResponse
    {
        $service = Service::query()->active()->where('slug', $request->string('service'))->firstOrFail();
        $professionalSlug = $request->string('professional')->toString();
        $professional = $professionalSlug === 'any'
            ? null
            : Professional::query()->active()->where('slug', $professionalSlug)->firstOrFail();
        $date = CarbonImmutable::createFromFormat('Y-m-d', $request->string('date'), config('app.business_timezone'))->startOfDay();

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'slots' => $availability->slots($date, $service, $professional),
        ]);
    }
}
