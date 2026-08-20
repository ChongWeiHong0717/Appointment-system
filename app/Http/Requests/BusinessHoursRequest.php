<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->business_id !== null;
    }

    public function rules(): array
    {
        return [
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'hours.*.is_closed' => ['required', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'required_unless:hours.*.is_closed,1', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'required_unless:hours.*.is_closed,1', 'date_format:H:i', 'after:hours.*.opens_at'],
        ];
    }
}
