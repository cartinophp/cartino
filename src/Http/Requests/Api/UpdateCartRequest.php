<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', Rule::exists(Customer::class, 'id')],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['sometimes', 'in:active,abandoned,recovered,completed'],
        ];
    }
}
