<?php

namespace App\Http\Requests\Staff;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|integer|exists:students,id', // the form sends the student id
            'class' => 'required|integer|exists:class_transactions,id', // the form sends the class_transactions id
            'dateTime' => BoundedDate::rules(extra: ['before:tomorrow']),
            'Price' => 'required|integer|min:0|max:2000000000',
        ];
    }
}
