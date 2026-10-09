<?php

namespace App\Http\Requests\Teacher;

use App\Http\Requests\AttendanceRequest;
use Illuminate\Support\Facades\DB;

class RecordAttendanceRequest extends AttendanceRequest
{
    /** A teacher records attendance only for a class they are mapped to. */
    public function authorize(): bool
    {
        return DB::table('mapping_class_teachers')
            ->where('class_id', $this->route('schedule')->class_id)
            ->where('user_id', $this->user()->id)
            ->exists();
    }

    // The attendance form is reached by POST, so "back" would be a GET on a POST-only URL (405): go to the class schedule list.
    protected function getRedirectUrl(): string
    {
        return route('viewScheduleClassTeacher', $this->route('schedule')->class_id);
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        session()->flash('error', $validator->errors()->first());
        parent::failedValidation($validator);
    }
}
