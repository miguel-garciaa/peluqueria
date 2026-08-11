<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'service' => ['required', 'string', 'exists:services,slug'],
            'professional' => ['required', 'string'],
        ];
    }
}
