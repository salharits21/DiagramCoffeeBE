<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization ditangani oleh middleware role
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/', 'min:7', 'unique:branches,phone'],
            'status' => ['sometimes', 'in:active,inactive'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i'],
        ];
    }
}
