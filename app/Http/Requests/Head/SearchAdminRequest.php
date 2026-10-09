<?php

namespace App\Http\Requests\Head;

use Illuminate\Foundation\Http\FormRequest;

class SearchAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['search' => 'nullable|string|max:100'];
    }
}
