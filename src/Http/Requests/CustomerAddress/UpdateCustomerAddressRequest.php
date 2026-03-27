<?php

namespace Cartino\Http\Requests\CustomerAddress;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:billing,shipping',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Address type is required.',
            'type.in' => 'Address type must be billing or shipping.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'address_line_1.required' => 'Address line 1 is required.',
            'city.required' => 'City is required.',
            'postal_code.required' => 'Postal code is required.',
            'country_code.required' => 'Country code is required.',
            'country_code.size' => 'Country code must be exactly 2 characters.',
        ];
    }
}
