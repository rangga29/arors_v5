<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrCarolusStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qrc_ucode' => ['nullable'],
            'qrc_bed' => ['required', 'unique:qr_caroluses,qrc_bed'],
            'qrc_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'qrc_bed.required' => 'Bed Harus Diisi',
            'qrc_bed.unique' => 'Bed Sudah Digunakan',
        ];
    }
}
