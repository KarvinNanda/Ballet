<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'typeID' => 'required|integer|exists:class_types,id',
            'inputPrice' => 'required|integer|min:0|max:2000000000',
        ];
    }

    // The edit page is reached by POST, so "back" would be a GET on a POST-only URL (405): go to the list.
    protected function getRedirectUrl(): string
    {
        return staff_route('class-type.index');
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        session()->flash('error', $validator->errors()->first());
        parent::failedValidation($validator);
    }
}
