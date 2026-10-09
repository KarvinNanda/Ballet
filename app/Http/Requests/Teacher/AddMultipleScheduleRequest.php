<?php

namespace App\Http\Requests\Teacher;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;

class AddMultipleScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'classId' => ['required', 'integer'],
            'dateTime' => BoundedDate::rules(),
            'ScheduleLoop' => ['required', 'integer', 'between:1,52'], // weekly, at most one year
        ];
    }
}
