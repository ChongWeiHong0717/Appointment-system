<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->isPlatformAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => str($this->input('slug', $this->input('name')))->slug()->toString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash:ascii', Rule::unique('businesses', 'slug')],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'timezone' => ['required', 'timezone'],
            'booking_interval_minutes' => ['required', 'integer', Rule::in([15, 30, 45, 60, 90, 120])],
        ];
    }
}
