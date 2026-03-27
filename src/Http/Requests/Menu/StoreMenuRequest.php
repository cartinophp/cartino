<?php

namespace Cartino\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'handle' => 'nullable|string|max:255|unique:menus,handle',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('cartino::validation.menu.title.required'),
            'title.string' => __('cartino::validation.menu.title.string'),
            'title.max' => __('cartino::validation.menu.title.max'),
            'handle.string' => __('cartino::validation.menu.handle.string'),
            'handle.max' => __('cartino::validation.menu.handle.max'),
            'handle.unique' => __('cartino::validation.menu.handle.unique'),
            'description.string' => __('cartino::validation.menu.description.string'),
            'settings.array' => __('cartino::validation.menu.settings.array'),
            'is_active.boolean' => __('cartino::validation.menu.is_active.boolean'),
        ];
    }
}
