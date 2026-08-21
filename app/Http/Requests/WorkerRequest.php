<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->business_id !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => [
                'integer',
                Rule::exists(Service::class, 'id')->where(fn ($query) => $query
                    ->where('business_id', $this->user()->business_id)
                    ->whereNull('deleted_at')),
            ],
        ];
    }
}
