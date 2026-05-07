<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminId = $this->route('admin');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $adminId],
            'password' => ['sometimes', 'string', Password::min(8), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).+$/', 'confirmed'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
        ];
    }
}
