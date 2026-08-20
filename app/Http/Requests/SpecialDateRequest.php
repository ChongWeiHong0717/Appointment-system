<?php

namespace App\Http\Requests;

use App\Models\SpecialDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecialDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->business_id !== null;
    }

    public function rules(): array
    {
        $specialDateId = $this->route('specialDate');

        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique(SpecialDate::class, 'date')
                    ->where(fn ($query) => $query->where('business_id', $this->user()->business_id))
                    ->ignore($specialDateId),
            ],
            'is_closed' => ['required', 'boolean'],
            'opens_at' => ['nullable', 'required_unless:is_closed,1', 'date_format:H:i'],
            'closes_at' => ['nullable', 'required_unless:is_closed,1', 'date_format:H:i', 'after:opens_at'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
