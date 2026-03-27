<?php

namespace Cartino\Http\Requests\Wishlist;

use Cartino\Enums\WishlistStatus;
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
            'customer_id' => ['required', Rule::exists(Customer::class, 'id')],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => ['nullable', Rule::enum(WishlistStatus::class)],
            'is_shared' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => __('cartino::validation.wishlist.customer_id.required'),
            'customer_id.exists' => __('cartino::validation.wishlist.customer_id.exists'),
            'name.required' => __('cartino::validation.wishlist.name.required'),
            'name.string' => __('cartino::validation.wishlist.name.string'),
            'name.max' => __('cartino::validation.wishlist.name.max'),
            'description.string' => __('cartino::validation.wishlist.description.string'),
            'description.max' => __('cartino::validation.wishlist.description.max'),
            'status.enum' => __('cartino::validation.wishlist.status.enum'),
            'is_shared.boolean' => __('cartino::validation.wishlist.is_shared.boolean'),
        ];
    }
}
