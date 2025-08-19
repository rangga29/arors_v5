<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:users,name'],
            'username' => ['required', 'unique:users,username'],
            'password' => ['required'],
            'repeat_password' => ['required_with:password', 'same:password'],
            'role' => ['required'],
            'created_by' => ['nullable'],
            'updated_by' => ['nullable']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama User Harus Diisi',
            'name.unique' => 'Nama User Sudah Digunakan',
            'username.required' => 'Username Harus Diisi',
            'username.unique' => 'Username Sudah Digunakan',
            'password.required' => 'Password Harus Diisi',
            'repeat_password.required_with' => 'Ulangi Password Harus Diisi',
            'repeat_password.same' => 'Password & Ulangi Password Tidak Sama',
            'role.required' => 'Role Harus Diisi'
        ];
    }
}
