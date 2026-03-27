<?php

namespace Cartino\Http\Requests;

use Cartino\Enums\CustomerStatus;
use Cartino\Enums\Gender;
use Cartino\Models\CustomerGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization should be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $customerId = $this->route('customer')->id ?? $this->route('customer');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'status' => ['required', Rule::enum(CustomerStatus::class)],
            'customer_group_id' => ['nullable', Rule::exists(CustomerGroup::class, 'id')],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'min:8'],
            'email_verified_at' => ['nullable', 'date'],
            'preferences' => ['nullable', 'array'],
            'preferences.marketing_emails' => ['boolean'],
            'preferences.sms_notifications' => ['boolean'],
            'preferences.newsletter' => ['boolean'],
            'meta' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
