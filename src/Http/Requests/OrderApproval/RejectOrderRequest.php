<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\OrderApproval;

use Illuminate\Foundation\Http\FormRequest;

class RejectOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $orderApproval = $this->route('orderApproval');

        return $this->user()->can('reject', $orderApproval);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'rejection_reason' => 'rejection reason',
            'notes' => 'internal notes',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'You must provide a reason for rejecting this order.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Add approver ID automatically
        $this->merge([
            'approver_id' => $this->user()->id,
            'rejected_at' => now(),
        ]);
    }
}
