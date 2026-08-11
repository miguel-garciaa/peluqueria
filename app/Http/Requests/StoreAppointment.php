<?php

namespace App\Http\Requests;

use App\Models\Professional;
use App\Models\Service;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointment extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^(?:\+34\s?)?[6789](?:[\s-]?\d){8}$/'],
            'serviceId' => ['required', 'string', Rule::exists('services', 'slug')->where('is_active', true)],
            'professionalId' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value !== 'any' && ! Professional::query()->active()->where('slug', $value)->exists()) {
                        $fail('El profesional seleccionado no está disponible.');
                    }
                },
            ],
            'customDetails' => [
                'nullable',
                'string',
                'max:100',
            ],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.today(config('app.business_timezone'))->format('Y-m-d'), 'before_or_equal:'.today(config('app.business_timezone'))->addDays(90)->format('Y-m-d')],
            'timeSlot' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->has('serviceId')) {
                return;
            }

            $service = Service::query()->where('slug', $this->string('serviceId'))->first();
            if ($service?->is_custom && trim((string) $this->input('customDetails')) === '') {
                $validator->errors()->add('customDetails', 'Cuéntanos qué necesitas para preparar la cita.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fullName' => trim((string) $this->input('fullName')),
            'phone' => trim((string) $this->input('phone')),
            'customDetails' => trim((string) $this->input('customDetails')),
        ]);
    }

    public function messages(): array
    {
        return [
            'fullName.required' => 'Escribe tu nombre completo.',
            'phone.regex' => 'Introduce un teléfono español válido.',
            'serviceId.required' => 'Selecciona un servicio.',
            'serviceId.exists' => 'El servicio seleccionado no está disponible.',
            'professionalId.required' => 'Selecciona un profesional o la primera disponibilidad.',
            'customDetails.max' => 'Utiliza un máximo de 100 caracteres.',
            'date.after_or_equal' => 'Selecciona una fecha válida.',
            'date.before_or_equal' => 'Puedes reservar con hasta 90 días de antelación.',
            'timeSlot.required' => 'Selecciona una hora disponible.',
        ];
    }
}
