<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Customer;
use Cartino\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'customer_id' => ['required', 'integer', Rule::exists(Customer::class, 'id')],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'pros' => ['nullable', 'string'],
            'cons' => ['nullable', 'string'],
            'verified_purchase' => ['boolean'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ];
    }
}
