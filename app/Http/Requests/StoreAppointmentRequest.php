<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^(?:\+34\s?)?[6789](?:[\s-]?\d){8}$/'],
            'serviceId' => ['required', 'string', Rule::in(['cut', 'balayage', 'keratin', 'barber', 'ritual', 'event'])],
            'professionalId' => ['required', 'string', Rule::in(['any', 'laura', 'dani', 'marta', 'alvaro'])],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'timeSlot' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['date', 'timeSlot'])) {
                return;
            }

            $date = CarbonImmutable::createFromFormat('Y-m-d H:i', $this->string('date').' '.$this->string('timeSlot'), config('app.timezone'));
            if (! $date) {
                return;
            }

            if ($date->isSunday()) {
                $validator->errors()->add('date', 'El estudio cierra los domingos.');

                return;
            }

            $startMinutes = $date->isSaturday() ? 9 * 60 : 9 * 60 + 30;
            $endMinutes = $date->isSaturday() ? 14 * 60 + 30 : 19 * 60 + 30;
            $requestedMinutes = $date->hour * 60 + $date->minute;
            if ($requestedMinutes < $startMinutes || $requestedMinutes > $endMinutes || $date->minute % 30 !== 0) {
                $validator->errors()->add('timeSlot', 'Selecciona una hora dentro del horario de apertura.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fullName' => trim((string) $this->input('fullName')),
            'phone' => trim((string) $this->input('phone')),
        ]);
    }

    public function messages(): array
    {
        return [
            'fullName.required' => 'Escribe tu nombre completo.',
            'fullName.min' => 'Escribe tu nombre completo.',
            'phone.regex' => 'Introduce un teléfono español válido.',
            'serviceId.required' => 'Selecciona un servicio.',
            'serviceId.in' => 'El servicio seleccionado no es válido.',
            'professionalId.required' => 'Selecciona un profesional o la primera disponibilidad.',
            'professionalId.in' => 'El profesional seleccionado no es válido.',
            'date.after_or_equal' => 'Selecciona una fecha válida.',
            'timeSlot.date_format' => 'Selecciona una hora válida.',
        ];
    }
}
