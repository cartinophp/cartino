<?php

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Permission;
use Cartino\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['nullable', 'string', Rule::exists(Role::class, 'name')],
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::exists(Permission::class, 'name')],
            'is_active' => 'boolean',
            'timezone' => 'nullable|string',
            'locale' => 'nullable|string|size:2',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome è obbligatorio.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',
            'email.required' => 'L\'email è obbligatoria.',
            'email.email' => 'L\'email deve essere un indirizzo email valido.',
            'email.unique' => 'Questa email è già registrata.',
            'password.required' => 'La password è obbligatoria.',
            'password.min' => 'La password deve contenere almeno 8 caratteri.',
            'password.confirmed' => 'La conferma della password non corrisponde.',
            'role.exists' => 'Il ruolo selezionato non esiste.',
            'permissions.*.exists' => 'Uno o più permessi selezionati non esistono.',
            'locale.size' => 'Il locale deve essere di 2 caratteri.',
        ];
    }
}
