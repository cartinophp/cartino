<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCartShippingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_method_id' => ['required', 'integer', Rule::exists(ShippingMethod::class, 'id')],
            'shipping_address' => ['required', 'array'],
            'shipping_address.first_name' => ['required', 'string', 'max:255'],
            'shipping_address.last_name' => ['required', 'string', 'max:255'],
            'shipping_address.address1' => ['required', 'string', 'max:255'],
            'shipping_address.address2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:100'],
            'shipping_address.state' => ['nullable', 'string', 'max:100'],
            'shipping_address.postcode' => ['required', 'string', 'max:20'],
            'shipping_address.country' => ['required', 'string', 'size:2'],
            'shipping_address.phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
