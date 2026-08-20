<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetBusinessAdminPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->isPlatformAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::min(12)],
        ];
    }
}
