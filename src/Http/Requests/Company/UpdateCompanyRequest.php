<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $this->user()->can('update', $company);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = $this->route('company')->id;

        return [
            // Basic Information
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('companies')->ignore($companyId)],
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Tax Information
            'vat_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'tax_id' => ['sometimes', 'nullable', 'string', 'max:50'],
            'tax_exempt' => ['sometimes', 'nullable', 'boolean'],
            'tax_exemptions' => ['sometimes', 'nullable', 'array'],
            'tax_exemptions.reason' => ['required_if:tax_exempt,true', 'string', 'in:nonprofit,government,resale,other'],
            'tax_exemptions.certificate_number' => ['required_if:tax_exempt,true', 'string', 'max:100'],
            'tax_exemptions.expires_at' => ['nullable', 'date', 'after:today'],

            // Contact Information
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],

            // Company Classification
            'type' => ['sometimes', 'required', 'string', Rule::in(['standard', 'enterprise', 'wholesale', 'reseller'])],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['active', 'suspended'])],
            'risk_level' => ['sometimes', 'nullable', 'string', Rule::in(['low', 'medium', 'high'])],

            // Financial Information
            'credit_limit' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'outstanding_balance' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'payment_terms_days' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:365'],
            'payment_method' => ['sometimes', 'nullable', 'string', Rule::in(['invoice', 'card', 'wire', 'check'])],

            // Approval Settings
            'approval_threshold' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'requires_approval' => ['sometimes', 'nullable', 'boolean'],

            // Addresses
            'billing_address' => ['sometimes', 'nullable', 'array'],
            'billing_address.street' => ['required_with:billing_address', 'string', 'max:255'],
            'billing_address.city' => ['required_with:billing_address', 'string', 'max:255'],
            'billing_address.state' => ['nullable', 'string', 'max:255'],
            'billing_address.zip' => ['required_with:billing_address', 'string', 'max:20'],
            'billing_address.country' => ['required_with:billing_address', 'string', 'size:2'],

            'shipping_address' => ['sometimes', 'nullable', 'array'],
            'shipping_address.street' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.city' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.state' => ['nullable', 'string', 'max:255'],
            'shipping_address.zip' => ['required_with:shipping_address', 'string', 'max:20'],
            'shipping_address.country' => ['required_with:shipping_address', 'string', 'size:2'],

            // Hierarchy
            'parent_company_id' => [
                'sometimes',
                'nullable',
                'exists:companies,id',
                Rule::notIn([$companyId]), // Cannot be parent of itself
            ],

            // Statistics (usually updated by system, but can be manually adjusted)
            'lifetime_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'order_count' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'last_order_at' => ['sometimes', 'nullable', 'date'],

            // Additional
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'settings' => ['sometimes', 'nullable', 'array'],
            'data' => ['sometimes', 'nullable', 'array'],
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
            'outstanding_balance' => 'outstanding balance',
            'payment_terms_days' => 'payment terms',
            'approval_threshold' => 'approval threshold',
            'parent_company_id' => 'parent company',
            'lifetime_value' => 'lifetime value',
            'order_count' => 'order count',
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
            'parent_company_id.not_in' => 'A company cannot be its own parent.',
        ];
    }
}
