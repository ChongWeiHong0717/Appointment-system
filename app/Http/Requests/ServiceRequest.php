<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Worker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->business_id !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists(Category::class, 'id')->where(fn ($query) => $query
                    ->where('business_id', $this->user()->business_id)
                    ->whereNull('deleted_at')),
            ],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:3000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'workers_required' => ['required', 'integer', 'min:1', 'max:50'],
            'qualified_worker_ids' => ['nullable', 'array'],
            'qualified_worker_ids.*' => [
                'integer',
                Rule::exists(Worker::class, 'id')->where(fn ($query) => $query
                    ->where('business_id', $this->user()->business_id)
                    ->whereNull('deleted_at')),
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
