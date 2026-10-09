<?php

namespace App\Http\Requests\Head;

use Illuminate\Foundation\Http\FormRequest;

class RuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'inputLanguage' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:50000'], // raw input; keeps HTMLPurifier work bounded
        ];
    }
}
