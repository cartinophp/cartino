<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MarkOrderAsShippedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Gestito da middleware di autenticazione
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tracking_number' => 'nullable|string',
            'carrier' => 'nullable|string',
            'shipped_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'shipped_at.date' => 'La data di spedizione deve essere una data valida.',
        ];
    }
}
