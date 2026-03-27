<?php

namespace Cartino\Http\Requests\Menu;

use Cartino\Enums\MenuItemType;
use Cartino\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'type' => ['required', 'string', Rule::enum(MenuItemType::class)],
            'parent_id' => ['nullable', Rule::exists(MenuItem::class, 'id')],
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'data' => 'nullable|array',
            'status' => 'string|in:active,inactive',
            'opens_in_new_window' => 'boolean',
            'css_class' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The menu item title is required.',
            'type.required' => 'The menu item type is required.',
            'type.in' => 'The selected type is invalid.',
            'parent_id.exists' => 'The selected parent menu item does not exist.',
        ];
    }
}
