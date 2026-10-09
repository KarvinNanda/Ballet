<?php

namespace App\Http\Requests\Teacher;

use App\Rules\BoundedDate;
use App\Rules\NotBeforeJakartaNow;
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
            'dateTime' => BoundedDate::rules(true, [new NotBeforeJakartaNow()]),
            'ScheduleLoop' => ['required', 'integer', 'between:1,52'], // weekly, at most one year
        ];
    }
}
