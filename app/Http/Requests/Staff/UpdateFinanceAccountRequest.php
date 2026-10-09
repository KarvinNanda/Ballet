<?php

namespace App\Http\Requests\Staff;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateFinanceAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        abort_unless($this->route('user')?->role === 'finance', 404);

        return Gate::allows('finance.manage');
    }

    public function rules(): array
    {
        return [
            'inputName' => 'required|string|max:255',
            'inputEmail' => ['required', 'email:filter', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'inputDate_of_Birth' => BoundedDate::rules(extra: ['before:tomorrow']),
            'inputAddress' => 'required|string|max:255',
            'inputBonus' => 'required|integer|min:0|max:100',
            'inputPhone' => 'required|numeric|digits_between:10,12',
        ];
    }
}
