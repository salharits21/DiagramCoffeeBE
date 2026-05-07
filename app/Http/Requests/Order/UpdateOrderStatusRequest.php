<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:preparing,ready,completed'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status baru wajib diisi.',
            'status.in' => 'Status harus salah satu dari: preparing, ready, completed.',
        ];
    }
}
