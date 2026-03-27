<?php

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkOrderActionRequest extends FormRequest
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
            'action' => 'required|in:cancel,mark_paid,mark_shipped,mark_delivered,export',
            'ids' => 'required|array|min:1',
            'ids.*' => ['integer', Rule::exists(Order::class, 'id')],
            'metadata' => 'nullable|array', // Per dati aggiuntivi come numeri di tracking
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'L\'azione è obbligatoria.',
            'action.in' => 'L\'azione deve essere una tra: cancel, mark_paid, mark_shipped, mark_delivered, export.',
            'ids.required' => 'Gli ID degli ordini sono obbligatori.',
            'ids.array' => 'Gli ID degli ordini devono essere un array.',
            'ids.min' => 'Devi selezionare almeno un ordine.',
            'ids.*.integer' => 'Ogni ID deve essere un numero intero.',
            'ids.*.exists' => 'Uno o più ordini selezionati non esistono.',
            'metadata.array' => 'I metadati devono essere un array.',
        ];
    }
}
