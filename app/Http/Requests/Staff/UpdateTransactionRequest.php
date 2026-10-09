<?php

namespace App\Http\Requests\Staff;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('transaction.edit-paid', $this->route('transaction'));
    }

    public function rules(): array
    {
        return [
            'inputDisc' => 'required|string|max:255',
            'inputStatus' => 'required|in:Paid,Unpaid,paid,unpaid',
            'inputJatuhTempo' => BoundedDate::rules(),
            'inputSenderName' => 'nullable|string|max:255',
            'inputBankName' => 'nullable|string|max:255',
            'inputQuota' => 'required|integer|min:1|max:24',
            'inputPrice' => 'required|integer|min:0|max:2000000000',
            'inputTanggalBayar' => BoundedDate::rules(false),
            'inputDesc' => 'nullable|string|max:255',
            'Type' => 'nullable|string|max:100',
        ];
    }
}
