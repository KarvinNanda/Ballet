<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class MapStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classId' => 'required|integer|exists:class_transactions,id',
            'studentId' => 'required|integer|exists:students,id',
        ];
    }
}
