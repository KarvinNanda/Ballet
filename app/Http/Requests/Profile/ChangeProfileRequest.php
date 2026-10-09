<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:filter', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'numeric', 'digits_between:10,12'],
        ];
    }
}
