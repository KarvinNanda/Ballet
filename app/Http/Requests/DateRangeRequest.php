<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DateRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // after_or_equal throws a TypeError when start_date is an array, so it applies only once start_date is a string
        // (an array start_date is already rejected by its own `string` rule).
        $end = 'required|string|date_format:Y-m-d';
        if (is_string($this->input('start_date'))) {
            $end .= '|after_or_equal:start_date';
        }

        return [
            'start_date' => 'required|string|date_format:Y-m-d',
            'end_date' => $end,
        ];
    }
}
