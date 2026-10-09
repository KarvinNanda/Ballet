<?php

namespace App\Http\Requests\Staff;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dateTime' => BoundedDate::rules(),
        ];
    }
}
