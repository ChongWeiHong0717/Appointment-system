<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->business_id !== null;
    }

    public function rules(): array
    {
        $timezone = $this->user()->business->timezone;

        return [
            'service_id' => [
                'required',
                'integer',
                Rule::exists(Service::class, 'id')->where(fn ($query) => $query
                    ->where('business_id', $this->user()->business_id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
            ],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now($timezone)->toDateString()],
            'start_time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:40', 'regex:/[0-9].*[0-9]/'],
            'customer_email' => ['nullable', 'email:rfc', 'max:255'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:3000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['customer_name', 'customer_phone', 'customer_email'] as $field) {
            $this->merge([$field => $this->filled($field) ? trim((string) $this->input($field)) : null]);
        }
    }
}
