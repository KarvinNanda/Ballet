<?php

namespace App\Http\Requests\Staff;

use App\Rules\BoundedDate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'LongName' => 'required|string|max:255',
            'nama_orang_tua' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'Email' => 'required|email:filter|max:255',
            'dob' => BoundedDate::rules(extra: ['before:tomorrow']),
            'Address' => 'required|string|max:255',
            'Phone1' => 'required|numeric|digits_between:10,12',
            'Whatsapp' => 'required|numeric|digits_between:10,12',
            'kode_pos' => 'required|numeric|min_digits:5|max_digits:10',
            'Quota' => 'required|integer|min:0|max:2000000000',
            'Quota_original' => 'required|integer|min:0|max:2000000000',
            'MaxQuota' => 'nullable|integer|min:0|max:2000000000',
            'is_new' => 'required|string|max:255',
            'status' => 'required|in:aktif,non-aktif,trial',
            'EnrollDate' => BoundedDate::rules(false),
            'accountno' => 'required|string|max:255',
            'sender' => 'required|string|max:255',
            'nis' => 'nullable|string|max:255',
            'ShortName' => 'nullable|string|max:255',
            'Phone2' => 'nullable|string|max:255',
            'Instagram' => 'nullable|string|max:255',
            'Line' => 'nullable|string|max:255',
            'bank' => 'nullable|string|max:255',
        ];
    }
}
