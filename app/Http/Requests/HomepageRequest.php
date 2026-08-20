<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomepageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->business_id !== null;
    }

    public function rules(): array
    {
        return [
            'hero_heading' => ['required', 'string', 'max:180'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'hero_cta_text' => ['required', 'string', 'max:80'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'about_heading' => ['required', 'string', 'max:180'],
            'about_body' => ['nullable', 'string', 'max:5000'],
            'why_choose_us' => ['required', 'array', 'size:3'],
            'why_choose_us.*' => ['required', 'string', 'max:120'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:170'],
        ];
    }
}
