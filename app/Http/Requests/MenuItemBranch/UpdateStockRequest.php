<?php

namespace App\Http\Requests\MenuItemBranch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_available' => ['sometimes', 'boolean'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:discount_type,percentage'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'required_if:discount_type,fixed'],
            'is_promo_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'discount_percentage.required_if' => 'Persentase diskon wajib diisi jika tipe diskon adalah percentage.',
            'discount_amount.required_if' => 'Nominal diskon wajib diisi jika tipe diskon adalah fixed.',
        ];
    }
}
