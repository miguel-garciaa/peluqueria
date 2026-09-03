<?php

namespace App\Http\Requests;

use App\Models\Professional;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.today(config('app.business_timezone'))->format('Y-m-d'), 'before_or_equal:'.today(config('app.business_timezone'))->addDays(90)->format('Y-m-d')],
            'service' => ['required', 'string', 'max:50', Rule::exists('services', 'slug')->where('is_active', true)],
            'professional' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value !== 'any' && ! Professional::query()->active()->where('slug', $value)->exists()) {
                        $fail('El profesional seleccionado no está disponible.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'service' => trim((string) $this->input('service')),
            'professional' => trim((string) $this->input('professional')),
        ]);
    }
}
