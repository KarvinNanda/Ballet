<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MapTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classId' => 'required|integer|exists:class_transactions,id',
            'teacherId' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'teacher')],
        ];
    }
}
