<?php

namespace App\Http\Requests\Staff;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;

class StoreMultipleScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classId' => 'required|integer|exists:class_transactions,id',
            'dateTime' => BoundedDate::rules(),
            'ScheduleLoop' => 'required|integer|between:1,52', // weekly, at most one year
        ];
    }
}
