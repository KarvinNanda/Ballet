<?php

namespace App\Http\Requests\Staff;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inputLongName' => 'required|string|max:255',
            'inputNickName' => 'required|string|max:255',
            'inputParentName' => 'required|string|max:255',
            'inputCity' => 'required|string|max:255',
            'inputEmail' => 'required|email:filter|max:255',
            'inputDate_of_Birth' => BoundedDate::rules(extra: ['before:tomorrow']),
            'inputAddress' => 'required|string|max:255',
            'inputPhone1' => 'required|numeric|digits_between:10,12',
            'inputWhatsapp' => 'required|numeric|digits_between:10,12',
            'inputPostalCode' => 'required|numeric|min_digits:5|max_digits:10',
            'inputNis' => 'nullable|string|max:255',
            'inputPhone2' => 'nullable|string|max:255',
            'inputInstagram' => 'nullable|string|max:254', // saved with a leading '@'
            'inputLine' => 'nullable|string|max:255',
            'inputRekening' => 'nullable|string|max:255',
            'inputBankName' => 'nullable|string|max:255',
            'inputNamaPengirim' => 'nullable|string|max:255',
            'terms_accepted' => 'accepted', // the parent agreed to the T&C shown in the form's modal; not stored
        ];
    }

    public function messages(): array
    {
        return ['terms_accepted.accepted' => 'You must confirm the terms and conditions.'];
    }
}
