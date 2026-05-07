<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).+$/', 'confirmed'],
            'branch_id' => ['required', 'exists:branches,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Admin harus ditugaskan ke sebuah cabang.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
        ];
    }
}
