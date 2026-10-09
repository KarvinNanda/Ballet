<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class FreezePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('class.freeze-price');
    }

    public function rules(): array
    {
        return [
            'inputPrice' => 'required|integer|min:0|max:2000000000',
        ];
    }
}
