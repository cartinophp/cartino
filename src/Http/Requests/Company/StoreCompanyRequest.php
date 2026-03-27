<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Company;

use Cartino\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Company::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic Information
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['nullable', 'string', 'max:255', 'unique:companies,handle'],
            'legal_name' => ['nullable', 'string', 'max:255'],

            // Tax Information
            'vat_number' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'tax_exempt' => ['nullable', 'boolean'],
            'tax_exemptions' => ['nullable', 'array'],
            'tax_exemptions.reason' => ['required_if:tax_exempt,true', 'string', 'in:nonprofit,government,resale,other'],
            'tax_exemptions.certificate_number' => ['required_if:tax_exempt,true', 'string', 'max:100'],
            'tax_exemptions.expires_at' => ['nullable', 'date', 'after:today'],

            // Contact Information
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],

            // Company Classification
            'type' => ['required', 'string', Rule::in(['standard', 'enterprise', 'wholesale', 'reseller'])],
            'status' => ['nullable', 'string', Rule::in(['active', 'suspended'])],
            'risk_level' => ['nullable', 'string', Rule::in(['low', 'medium', 'high'])],

            // Financial Information
            'credit_limit' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'payment_method' => ['nullable', 'string', Rule::in(['invoice', 'card', 'wire', 'check'])],

            // Approval Settings
            'approval_threshold' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'requires_approval' => ['nullable', 'boolean'],

            // Addresses
            'billing_address' => ['nullable', 'array'],
            'billing_address.street' => ['required_with:billing_address', 'string', 'max:255'],
            'billing_address.city' => ['required_with:billing_address', 'string', 'max:255'],
            'billing_address.state' => ['nullable', 'string', 'max:255'],
            'billing_address.zip' => ['required_with:billing_address', 'string', 'max:20'],
            'billing_address.country' => ['required_with:billing_address', 'string', 'size:2'],

            'shipping_address' => ['nullable', 'array'],
            'shipping_address.street' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.city' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.state' => ['nullable', 'string', 'max:255'],
            'shipping_address.zip' => ['required_with:shipping_address', 'string', 'max:20'],
            'shipping_address.country' => ['required_with:shipping_address', 'string', 'size:2'],

            // Hierarchy
            'parent_company_id' => ['nullable', 'exists:companies,id'],

            // Additional
            'notes' => ['nullable', 'string', 'max:5000'],
            'settings' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'company name',
            'legal_name' => 'legal company name',
            'vat_number' => 'VAT number',
            'tax_id' => 'tax ID',
            'credit_limit' => 'credit limit',
            'payment_terms_days' => 'payment terms',
            'approval_threshold' => 'approval threshold',
            'parent_company_id' => 'parent company',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'tax_exemptions.reason.required_if' => 'Tax exemption reason is required when company is tax exempt.',
            'tax_exemptions.certificate_number.required_if' => 'Tax exemption certificate is required when company is tax exempt.',
            'billing_address.country.size' => 'Country must be a valid 2-letter ISO code.',
            'shipping_address.country.size' => 'Country must be a valid 2-letter ISO code.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate handle from name if not provided
        if (! $this->handle && $this->name) {
            $this->merge([
                'handle' => Str::slug($this->name),
            ]);
        }

        // Set defaults
        $this->merge([
            'status' => $this->status ?? 'active',
            'type' => $this->type ?? 'standard',
            'risk_level' => $this->risk_level ?? 'low',
            'requires_approval' => $this->requires_approval ?? false,
            'tax_exempt' => $this->tax_exempt ?? false,
        ]);
    }
}
