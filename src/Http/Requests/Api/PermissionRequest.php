<?php

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-permissions');
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'updateRolePermissions' => [
                'permissions' => 'required|array',
                'permissions.*' => 'string|max:255',
                'inherit_from' => 'nullable|array',
                'inherit_from.*' => ['integer|', Rule::exists(Role::class, 'id')],
            ],
            'generatePermissions' => [
                'force_regenerate' => 'nullable|boolean',
                'groups' => 'nullable|array',
                'groups.*' => 'string|in:content,collections,commerce,customers,users,assets,forms,settings,roles,reports',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'I permessi sono obbligatori',
            'permissions.array' => 'I permessi devono essere un array',
            'permissions.*.string' => 'Ogni permesso deve essere una stringa',
            'inherit_from.array' => 'L\'ereditarietà deve essere un array',
            'inherit_from.*.exists' => 'Il ruolo da cui ereditare non esiste',
            'groups.*.in' => 'Gruppo di permessi non valido',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Pulisci i permessi da spazi extra
        if ($this->has('permissions')) {
            $this->merge([
                'permissions' => array_map('trim', $this->permissions ?? []),
            ]);
        }
    }
}
