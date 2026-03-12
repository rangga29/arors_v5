<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:users,name,' . $this->user->id],
            'password' => ['nullable'],
            'repeat_password' => ['required_with:password', 'same:password'],
            'created_by' => ['nullable'],
            'updated_by' => ['nullable']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama User Harus Diisi',
            'name.unique' => 'Nama User Sudah Digunakan',
            'password.required' => 'Password Harus Diisi',
            'repeat_password.required_with' => 'Ulangi Password Harus Diisi',
            'repeat_password.same' => 'Password & Ulangi Password Tidak Sama'
        ];
    }
}
