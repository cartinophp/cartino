<?php

namespace Cartino\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_types',
            'description' => 'nullable|string|max:1000',
            'is_enabled' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The product type name is required.',
            'slug.required' => 'The slug is required.',
            'slug.unique' => 'This slug is already taken.',
        ];
    }
}
