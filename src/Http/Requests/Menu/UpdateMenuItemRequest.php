<?php

namespace Cartino\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
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
            'type' => 'required|string|in:link,collection,entry,external',
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
        ];
    }
}
