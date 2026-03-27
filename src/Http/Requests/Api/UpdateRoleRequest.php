<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
        $roleId = $this->route('role');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string|', Rule::exists(Permission::class, 'name')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Questo nome del ruolo è già in uso.',
            'name.max' => 'Il nome del ruolo non può superare i 255 caratteri.',
            'display_name.max' => 'Il nome visualizzato non può superare i 255 caratteri.',
            'permissions.array' => 'I permessi devono essere un array.',
            'permissions.*.exists' => 'Uno o più permessi selezionati non esistono.',
        ];
    }
}
