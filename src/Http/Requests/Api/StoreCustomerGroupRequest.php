<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_default' => ['boolean'],
        ];
    }
}
