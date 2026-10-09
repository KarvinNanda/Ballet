<?php

namespace App\Http\Requests\Teacher;

use App\Rules\BoundedDate;
use App\Rules\NotBeforeJakartaNow;
use Illuminate\Foundation\Http\FormRequest;

class AddScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'dateTime' => BoundedDate::rules(true, [new NotBeforeJakartaNow()]),
        ];
    }
}
