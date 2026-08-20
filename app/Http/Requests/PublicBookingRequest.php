<?php

namespace App\Http\Requests;

use App\Models\Business;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('business') instanceof Business && $this->route('business')->is_active;
    }

    public function rules(): array
    {
        /** @var Business $business */
        $business = $this->route('business');
        $today = now($business->timezone)->toDateString();
        $lastDate = now($business->timezone)->addDays(120)->toDateString();

        return [
            'service_id' => [
                'required',
                'integer',
                Rule::exists(Service::class, 'id')->where(fn ($query) => $query
                    ->where('business_id', $business->id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
            ],
            'appointment_date' => ['required', 'date_format:Y-m-d', "after_or_equal:{$today}", "before_or_equal:{$lastDate}"],
            'start_time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:40', 'regex:/[0-9].*[0-9]/'],
            'customer_email' => ['nullable', 'email:rfc', 'max:255'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_name' => trim((string) $this->input('customer_name')),
            'customer_phone' => trim((string) $this->input('customer_phone')),
            'customer_email' => $this->filled('customer_email') ? trim((string) $this->input('customer_email')) : null,
        ]);
    }
}
