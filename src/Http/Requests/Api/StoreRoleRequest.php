<?php

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:roles,name',
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
            'name.required' => 'Il nome del ruolo è obbligatorio.',
            'name.unique' => 'Questo nome del ruolo è già in uso.',
            'name.max' => 'Il nome del ruolo non può superare i 255 caratteri.',
            'display_name.max' => 'Il nome visualizzato non può superare i 255 caratteri.',
            'permissions.array' => 'I permessi devono essere un array.',
            'permissions.*.exists' => 'Uno o più permessi selezionati non esistono.',
        ];
    }
}
