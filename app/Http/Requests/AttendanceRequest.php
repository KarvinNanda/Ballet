<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Shared attendance rules; the Staff and Teacher subclasses decide who may submit. */
class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|array|max:200',
            'student_id.*' => 'integer',
            'check' => 'nullable|array|max:200',
            'check.*' => 'in:on,off',
            'keterangan' => 'nullable|array|max:200',
            'keterangan.*' => 'nullable|in:Select...,Attend,Absent,Permission,Sick',
            'notes' => 'nullable|array|max:200',
            'notes.*' => 'nullable|string|max:255',
        ];
    }
}
