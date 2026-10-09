<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inputType' => 'required|integer|exists:class_types,id',
            'inputTeacher' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'teacher')],
        ];
    }
}
