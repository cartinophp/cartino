<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\OrderApproval;

use Illuminate\Foundation\Http\FormRequest;

class ApproveOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $orderApproval = $this->route('orderApproval');

        return $this->user()->can('approve', $orderApproval);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'approval_reason' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'approval_reason' => 'approval reason',
            'notes' => 'internal notes',
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
            'approved_at' => now(),
        ]);
    }
}
