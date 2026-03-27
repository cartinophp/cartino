<?php

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
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
            'roles' => 'required|array',
            'roles.*' => ['string', Rule::exists(Role::class, 'name')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'roles.required' => 'I ruoli sono obbligatori.',
            'roles.array' => 'I ruoli devono essere un array.',
            'roles.*.exists' => 'Uno o più ruoli selezionati non esistono.',
        ];
    }
}
