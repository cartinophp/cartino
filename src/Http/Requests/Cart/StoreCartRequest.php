<?php

namespace Cartino\Http\Requests\Cart;

use Cartino\Enums\CartStatus;
use Cartino\Models\Customer;
use Cartino\Models\Product;
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
            'session_id' => 'nullable|string|max:255',
            'customer_id' => ['nullable', Rule::exists(Customer::class, 'id')],
            'email' => 'nullable|email|max:255',
            'status' => ['nullable', Rule::enum(CartStatus::class)],
            'items' => 'nullable|array',
            'items.*.product_id' => ['required', Rule::exists(Product::class, 'id')],
            'items.*.quantity' => 'required|integer|min:1|max:999',
            'items.*.price' => 'required|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'shipping_address' => 'nullable|array',
            'billing_address' => 'nullable|array',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'session_id.string' => __('cartino::validation.cart.session_id.string'),
            'session_id.max' => __('cartino::validation.cart.session_id.max'),
            'customer_id.exists' => __('cartino::validation.cart.customer_id.exists'),
            'email.email' => __('cartino::validation.cart.email.email'),
            'email.max' => __('cartino::validation.cart.email.max'),
            'status.enum' => __('cartino::validation.cart.status.enum'),
            'items.array' => __('cartino::validation.cart.items.array'),
            'items.*.product_id.required' => __('cartino::validation.cart.items.product_id.required'),
            'items.*.product_id.exists' => __('cartino::validation.cart.items.product_id.exists'),
            'items.*.quantity.required' => __('cartino::validation.cart.items.quantity.required'),
            'items.*.quantity.integer' => __('cartino::validation.cart.items.quantity.integer'),
            'items.*.quantity.min' => __('cartino::validation.cart.items.quantity.min'),
            'items.*.quantity.max' => __('cartino::validation.cart.items.quantity.max'),
            'items.*.price.required' => __('cartino::validation.cart.items.price.required'),
            'items.*.price.numeric' => __('cartino::validation.cart.items.price.numeric'),
            'items.*.price.min' => __('cartino::validation.cart.items.price.min'),
            'subtotal.numeric' => __('cartino::validation.cart.subtotal.numeric'),
            'subtotal.min' => __('cartino::validation.cart.subtotal.min'),
            'tax_amount.numeric' => __('cartino::validation.cart.tax_amount.numeric'),
            'tax_amount.min' => __('cartino::validation.cart.tax_amount.min'),
            'shipping_amount.numeric' => __('cartino::validation.cart.shipping_amount.numeric'),
            'shipping_amount.min' => __('cartino::validation.cart.shipping_amount.min'),
            'discount_amount.numeric' => __('cartino::validation.cart.discount_amount.numeric'),
            'discount_amount.min' => __('cartino::validation.cart.discount_amount.min'),
            'total_amount.numeric' => __('cartino::validation.cart.total_amount.numeric'),
            'total_amount.min' => __('cartino::validation.cart.total_amount.min'),
            'currency.string' => __('cartino::validation.cart.currency.string'),
            'currency.size' => __('cartino::validation.cart.currency.size'),
            'shipping_address.array' => __('cartino::validation.cart.shipping_address.array'),
            'billing_address.array' => __('cartino::validation.cart.billing_address.array'),
            'metadata.array' => __('cartino::validation.cart.metadata.array'),
        ];
    }
}
