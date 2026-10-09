<?php

namespace App\Http\Requests\Finance;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;

class MarkTransactionPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // role:finance middleware guards the group
    }

    public function rules(): array
    {
        return [
            'datePaid' => BoundedDate::rules(),
            'inputBankName' => 'required|string|max:255',
            'inputSenderName' => 'required|string|max:255',
            'inputQuota' => 'required|integer|min:1|max:24',
            'Type' => 'required|string|max:100',
        ];
    }
}
