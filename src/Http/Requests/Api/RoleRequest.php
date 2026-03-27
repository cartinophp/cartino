<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'store' => $this->user()->can('create roles'),
            'update' => $this->user()->can('edit roles'),
            default => false,
        };
    }

    public function rules(): array
    {
        $roleId = $this->route('id');

        return match ($this->route()->getActionMethod()) {
            'store' => [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9_-]+$/',
                    Rule::unique('roles', 'name')->where('guard_name', 'api'),
                ],
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'permissions' => 'nullable|array',
                'permissions.*' => ['string|', Rule::exists(Permission::class, 'name')],
            ],
            'update' => [
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'permissions' => 'nullable|array',
                'permissions.*' => ['string|', Rule::exists(Permission::class, 'name')],
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del ruolo è obbligatorio',
            'name.unique' => 'Esiste già un ruolo con questo nome',
            'name.regex' => 'Il nome può contenere solo lettere minuscole, numeri, trattini e underscore',
            'display_name.required' => 'Il nome visualizzato è obbligatorio',
            'display_name.max' => 'Il nome visualizzato non può superare i 255 caratteri',
            'description.max' => 'La descrizione non può superare i 500 caratteri',
            'permissions.array' => 'I permessi devono essere un array',
            'permissions.*.exists' => 'Uno o più permessi non sono validi',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Converte il nome in lowercase e rimuove spazi
        if ($this->has('name')) {
            $this->merge([
                'name' => strtolower(str_replace(' ', '_', trim($this->name))),
            ]);
        }

        // Pulisce i permessi da duplicati
        if ($this->has('permissions')) {
            $this->merge([
                'permissions' => array_unique(array_filter($this->permissions ?? [])),
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Verifica che non si stia tentando di modificare il nome del ruolo super
            if ($this->route()->getActionMethod() === 'update') {
                $roleId = $this->route('id');
                $role = Role::find($roleId);

                if ($role && $role->name === 'super' && ! $this->user()->hasRole('super')) {
                    $validator->errors()->add('role', 'Non puoi modificare il ruolo Super User');
                }
            }

            // Verifica che non si stia assegnando il permesso 'super' a ruoli non autorizzati
            if ($this->has('permissions') && in_array('super', $this->permissions ?? [])) {
                if (! $this->user()->hasRole('super')) {
                    $validator->errors()->add('permissions', 'Solo i Super User possono assegnare il permesso super');
                }
            }
        });
    }
}
