<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Currency;
use Cartino\Models\Customer;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists(Customer::class, 'id')],
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'product_variant_id' => ['nullable', 'integer', Rule::exists(ProductVariant::class, 'id')],
            'billing_interval' => ['required', 'string', 'in:day,week,month,year'],
            'billing_interval_count' => ['required', 'integer', 'min:1', 'max:365'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency_id' => ['required', 'integer', Rule::exists(Currency::class, 'id')],
            'trial_end_at' => ['nullable', 'date', 'after:now'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_details' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
        ];
    }
}
