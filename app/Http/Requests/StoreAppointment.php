<?php

namespace App\Http\Requests;

use App\Models\Professional;
use App\Models\Service;
use App\Services\AppointmentAvailability;
use App\Support\SpanishPhone;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Throwable;

class StoreAppointment extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(AppointmentAvailability $availability): array
    {
        $timezone = config('app.business_timezone');
        $today = CarbonImmutable::today($timezone);

        return [
            'fullName' => ['bail', 'required', 'string', 'min:2', 'max:120'],
            'phone' => ['bail', 'required', 'string', 'max:16', 'regex:/^\+34 [6789]\d{2}(?: \d{2}){3}$/'],
            'serviceId' => [
                'bail',
                'required',
                'string',
                'max:50',
                Rule::exists('services', 'slug')->where('is_active', true),
            ],
            'professionalId' => [
                'bail',
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === 'any') {
                        return;
                    }

                    $exists = Professional::query()
                        ->active()
                        ->where('slug', $value)
                        ->exists();

                    if (! $exists) {
                        $fail('El profesional seleccionado no está disponible.');
                    }
                },
            ],
            'customDetails' => ['nullable', 'string', 'max:100'],
            'date' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.$today->format('Y-m-d'),
                'before_or_equal:'.$today->addDays(90)->format('Y-m-d'),
            ],
            'timeSlot' => [
                'bail',
                'required',
                'date_format:H:i',
                function (string $attribute, mixed $value, Closure $fail) use ($availability, $timezone): void {
                    $service = Service::query()
                        ->active()
                        ->where('slug', $this->string('serviceId'))
                        ->first();

                    if (! $service) {
                        return;
                    }

                    $professionalSlug = (string) $this->input('professionalId');
                    $professional = $professionalSlug === 'any'
                        ? null
                        : Professional::query()
                            ->active()
                            ->where('slug', $professionalSlug)
                            ->first();

                    if ($professionalSlug !== 'any' && ! $professional) {
                        return;
                    }

                    try {
                        $startsAt = CarbonImmutable::createFromFormat(
                            '!Y-m-d H:i',
                            $this->string('date').' '.$value,
                            $timezone,
                        );
                    } catch (Throwable) {
                        return;
                    }

                    if (! $startsAt->isFuture()) {
                        $fail('La fecha y hora deben ser futuras.');

                        return;
                    }

                    $isAvailable = collect($availability->slots(
                        $startsAt->startOfDay(),
                        $service,
                        $professional,
                    ))->contains(fn (array $slot): bool => $slot['time'] === $value);

                    if (! $isAvailable) {
                        $fail('La hora seleccionada está fuera del horario laboral o ya no está disponible.');
                    }
                },
            ],
            'paymentMethod' => [
                'required',
                Rule::in(config('payments.bizum_enabled') ? ['cash', 'bizum'] : ['cash']),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('serviceId')) {
                return;
            }

            $service = Service::query()
                ->active()
                ->where('slug', $this->string('serviceId'))
                ->first();

            if ($service?->is_custom && blank($this->input('customDetails'))) {
                $validator->errors()->add(
                    'customDetails',
                    'Cuéntanos qué necesitas para preparar la cita.',
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $fullName = $this->plainText($this->input('fullName'));

        $this->merge([
            'fullName' => Str::squish($fullName ?? ''),
            'phone' => SpanishPhone::format((string) $this->input('phone')),
            'serviceId' => trim((string) $this->input('serviceId')),
            'professionalId' => trim((string) $this->input('professionalId')),
            'customDetails' => $this->plainText($this->input('customDetails')),
            'paymentMethod' => trim((string) ($this->input('paymentMethod') ?: 'cash')),
        ]);
    }

    private function plainText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = html_entity_decode(
            (string) $value,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );
        $text = strip_tags($text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
        $text = trim($text);

        return $text === '' ? null : $text;
    }

    public function messages(): array
    {
        return [
            'fullName.required' => 'Escribe tu nombre completo.',
            'fullName.max' => 'El nombre no puede superar los 120 caracteres.',
            'phone.regex' => 'Introduce un teléfono español válido.',
            'serviceId.required' => 'Selecciona un servicio.',
            'serviceId.exists' => 'El servicio seleccionado no está disponible.',
            'professionalId.required' => 'Selecciona un profesional o la primera disponibilidad.',
            'customDetails.max' => 'Utiliza un máximo de 100 caracteres.',
            'date.date_format' => 'La fecha no tiene un formato válido.',
            'date.after_or_equal' => 'La fecha debe ser actual o futura.',
            'date.before_or_equal' => 'Puedes reservar con hasta 90 días de antelación.',
            'timeSlot.required' => 'Selecciona una hora disponible.',
            'timeSlot.date_format' => 'La hora seleccionada no tiene un formato válido.',
            'paymentMethod.required' => 'Selecciona una forma de pago.',
            'paymentMethod.in' => 'Bizum estará disponible próximamente. Selecciona el pago en efectivo.',
        ];
    }
}
