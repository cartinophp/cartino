<?php

namespace Cartino\Http\Requests\Wishlist;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:active,inactive',
            'is_shared' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The wishlist name is required.',
            'name.max' => 'The wishlist name cannot exceed 255 characters.',
            'description.max' => 'The description cannot exceed 1000 characters.',
            'status.in' => 'The status must be either active or inactive.',
        ];
    }
}
