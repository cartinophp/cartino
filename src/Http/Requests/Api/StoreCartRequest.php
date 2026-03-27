<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', Rule::exists(Customer::class, 'id')],
            'session_id' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'currency_code' => ['required', 'string', 'size:3'],
        ];
    }
}
