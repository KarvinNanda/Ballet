<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inputName' => 'required|string|max:255',
            'inputPrice' => 'required|integer|min:0|max:2000000000',
        ];
    }
}
