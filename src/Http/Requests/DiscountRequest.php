<?php

declare(strict_types=1);

namespace Cartino\Http\Requests;

use Cartino\Enums\DiscountType;
use Cartino\Models\Customer;
use Cartino\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $discountId = $this->route('discount')?->id;

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'code' => [
                'nullable',
                'string',
                'max:50',
                'alpha_num',
                Rule::unique('discounts', 'code')->ignore($discountId),
            ],
            'type' => ['required', Rule::enum(DiscountType::class)],
            'value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === 'percentage' && $value > 100) {
                        $fail(__('discount.validation.percentage_max'));
                    }
                },
            ],
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'maximum_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_customer' => 'nullable|integer|min:0',
            'is_enabled' => 'boolean',
            'starts_at' => 'nullable|date|after_or_equal:today',
            'expires_at' => 'nullable|date|after:starts_at',
            'eligible_customers' => 'nullable|array',
            'eligible_customers.*' => ['integer', Rule::exists(Customer::class, 'id')],
            'eligible_products' => 'nullable|array',
            'eligible_products.*' => ['integer', Rule::exists(Product::class, 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('discount.validation.name_required'),
            'code.unique' => __('discount.validation.code_unique'),
            'code.alpha_num' => __('discount.validation.code_format'),
            'type.required' => __('discount.validation.type_required'),
            'type.in' => __('discount.validation.type_invalid'),
            'value.required' => __('discount.validation.value_required'),
            'value.numeric' => __('discount.validation.value_numeric'),
            'expires_at.after' => __('discount.validation.expires_after_starts'),
            'eligible_customers.*.exists' => __('discount.validation.customer_not_found'),
            'eligible_products.*.exists' => __('discount.validation.product_not_found'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('discount.fields.name'),
            'description' => __('discount.fields.description'),
            'code' => __('discount.fields.code'),
            'type' => __('discount.fields.type'),
            'value' => __('discount.fields.value'),
            'minimum_order_amount' => __('discount.fields.minimum_order_amount'),
            'maximum_discount_amount' => __('discount.fields.maximum_discount_amount'),
            'usage_limit' => __('discount.fields.usage_limit'),
            'usage_limit_per_customer' => __('discount.fields.usage_limit_per_customer'),
            'is_enabled' => __('discount.fields.is_enabled'),
            'starts_at' => __('discount.fields.starts_at'),
            'expires_at' => __('discount.fields.expires_at'),
            'eligible_customers' => __('discount.fields.eligible_customers'),
            'eligible_products' => __('discount.fields.eligible_products'),
        ];
    }
}
