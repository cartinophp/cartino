<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'product_variant_id' => ['nullable', 'integer', Rule::exists(ProductVariant::class, 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
            'options' => ['nullable', 'array'],
        ];
    }
}
