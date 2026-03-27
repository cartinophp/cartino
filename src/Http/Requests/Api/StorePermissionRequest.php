<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'group' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del permesso è obbligatorio.',
            'name.unique' => 'Questo nome del permesso è già in uso.',
            'name.max' => 'Il nome del permesso non può superare i 255 caratteri.',
            'display_name.max' => 'Il nome visualizzato non può superare i 255 caratteri.',
            'group.max' => 'Il gruppo non può superare i 100 caratteri.',
        ];
    }
}
