<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
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
        $permissionId = $this->route('permission');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permissionId),
            ],
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
            'name.unique' => 'Questo nome del permesso è già in uso.',
            'name.max' => 'Il nome del permesso non può superare i 255 caratteri.',
            'display_name.max' => 'Il nome visualizzato non può superare i 255 caratteri.',
            'group.max' => 'Il gruppo non può superare i 100 caratteri.',
        ];
    }
}
