<?php

namespace App\Http\Requests\Staff;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inputName' => 'required|string|max:255',
            'inputEmail' => 'required|email:filter|max:255|unique:users,email',
            'inputDate_of_Birth' => BoundedDate::rules(extra: ['before:tomorrow']),
            'inputAddress' => 'required|string|max:255',
            'inputPhone' => 'required|numeric|digits_between:10,12',
        ];
    }
}
