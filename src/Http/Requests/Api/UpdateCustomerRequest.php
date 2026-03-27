<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer');

        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'status' => 'string|in:active,inactive',
            'accepts_marketing' => 'boolean',
            'customer_group_id' => ['nullable|integer|', Rule::exists(CustomerGroup::class, 'id')],
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.max' => 'Il nome non può superare i 255 caratteri.',
            'last_name.max' => 'Il cognome non può superare i 255 caratteri.',
            'email.email' => 'L\'email deve essere un indirizzo email valido.',
            'email.unique' => 'Questa email è già registrata.',
            'phone.max' => 'Il numero di telefono non può superare i 20 caratteri.',
            'date_of_birth.date' => 'La data di nascita deve essere una data valida.',
            'date_of_birth.before' => 'La data di nascita deve essere precedente a oggi.',
            'gender.in' => 'Il genere deve essere uno tra: male, female, other.',
            'customer_group_id.exists' => 'Il gruppo clienti selezionato non esiste.',
            'tags.*.max' => 'Ogni tag non può superare i 100 caratteri.',
        ];
    }
}
