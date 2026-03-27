<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PermissionMatrixRequest extends FormRequest
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
            'matrix' => 'required|array',
            'matrix.*.resource' => 'required|string',
            'matrix.*.permissions' => 'required|array',
            'matrix.*.permissions.create' => 'boolean',
            'matrix.*.permissions.read' => 'boolean',
            'matrix.*.permissions.update' => 'boolean',
            'matrix.*.permissions.delete' => 'boolean',
            'matrix.*.permissions.all' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'matrix.required' => 'La matrice dei permessi è obbligatoria.',
            'matrix.array' => 'La matrice dei permessi deve essere un array.',
            'matrix.*.resource.required' => 'Il nome della risorsa è obbligatorio.',
            'matrix.*.permissions.required' => 'I permessi per la risorsa sono obbligatori.',
            'matrix.*.permissions.array' => 'I permessi devono essere un array.',
            'matrix.*.permissions.*.boolean' => 'I permessi devono essere valori booleani.',
        ];
    }
}
