<?php

namespace Cartino\Http\Requests\Menu;

use Cartino\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderMenuItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.id' => ['required|integer|', Rule::exists(MenuItem::class, 'id')],
            'items.*.children' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Items array is required.',
            'items.*.id.required' => 'Each item must have an id.',
            'items.*.id.exists' => 'One or more menu items do not exist.',
        ];
    }
}
