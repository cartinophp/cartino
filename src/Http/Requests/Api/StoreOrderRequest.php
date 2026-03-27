<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Currency;
use Cartino\Models\Customer;
use Cartino\Models\Order;
use Cartino\Models\Product;
use Cartino\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', Rule::exists(Customer::class, 'id')],
            'customer_email' => ['required', 'email'],
            'customer_details' => ['required', 'array'],
            'currency_id' => ['required', Rule::exists(Currency::class, 'id')],
            'site_id' => ['nullable', Rule::exists(Site::class, 'id')],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_total' => ['required', 'numeric', 'min:0'],
            'shipping_total' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'shipping_address' => ['required', 'array'],
            'billing_address' => ['required', 'array'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', Rule::exists(Product::class, 'id')],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
