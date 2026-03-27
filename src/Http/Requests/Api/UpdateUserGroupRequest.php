<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserGroupRequest extends FormRequest
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
        $groupId = $this->route('userGroup');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('user_groups', 'name')->ignore($groupId),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string|', Rule::exists(Permission::class, 'name')],
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Questo nome del gruppo è già in uso.',
            'name.max' => 'Il nome del gruppo non può superare i 255 caratteri.',
            'permissions.array' => 'I permessi devono essere un array.',
            'permissions.*.exists' => 'Uno o più permessi selezionati non esistono.',
            'metadata.array' => 'I metadati devono essere un array.',
        ];
    }
}
