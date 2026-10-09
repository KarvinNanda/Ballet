<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('stock.manage');
    }

    public function rules(): array
    {
        return [
            'inputName' => 'required|string|max:255',
            'inputSize' => 'required|string|max:255',
            'inputQty' => 'required|integer|min:0|max:2000000000', // 0 = sold out; sales bring items there
        ];
    }
}
