<?php

namespace App\Http\Requests\Staff;

use App\Http\Requests\AttendanceRequest;
use Illuminate\Support\Facades\Gate;

class RecordAttendanceRequest extends AttendanceRequest
{
    public function authorize(): bool
    {
        return Gate::allows('attendance.record');
    }
}
