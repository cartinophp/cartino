<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\CP;

use Cartino\Enums\CustomerStatus;
use Cartino\Enums\Gender;
use Cartino\Models\Customer;
use Cartino\Models\CustomerGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create_customers');
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(Customer::class, 'email')->ignore($customerId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', Rule::enum(Gender::class)],
            'status' => ['required', 'string', Rule::enum(CustomerStatus::class)],
            'customer_group_id' => ['nullable', Rule::exists(CustomerGroup::class, 'id')],
            'notes' => ['nullable', 'string', 'max:1000'],
            'accepts_marketing' => ['boolean'],
            'tax_exempt' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'password' => [$customerId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Il nome è obbligatorio.',
            'last_name.required' => 'Il cognome è obbligatorio.',
            'email.required' => 'L\'email è obbligatoria.',
            'email.email' => 'L\'email deve essere valida.',
            'email.unique' => 'Questa email è già in uso.',
            'status.required' => 'Lo status è obbligatorio.',
            'status.in' => 'Lo status deve essere attivo o inattivo.',
            'phone.max' => 'Il telefono non può superare i 20 caratteri.',
            'date_of_birth.date' => 'La data di nascita deve essere una data valida.',
            'gender.in' => 'Il genere deve essere maschio, femmina o altro.',
            'password.required' => 'La password è obbligatoria per i nuovi clienti.',
            'password.confirmed' => 'La conferma password non corrisponde.',
        ];
    }
}
