<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists(Customer::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'is_public' => ['boolean'],
            'status' => ['required', 'in:active,archived'],
        ];
    }
}
