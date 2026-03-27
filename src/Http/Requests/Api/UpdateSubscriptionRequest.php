<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['sometimes', 'integer', Rule::exists(Product::class, 'id')],
            'product_variant_id' => ['sometimes', 'nullable', 'integer', Rule::exists(ProductVariant::class, 'id')],
            'billing_interval' => ['sometimes', 'string', 'in:day,week,month,year'],
            'billing_interval_count' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'nullable', 'string', 'max:255'],
            'payment_details' => ['sometimes', 'nullable', 'array'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'data' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
