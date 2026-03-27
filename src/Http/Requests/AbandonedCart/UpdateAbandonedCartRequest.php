<?php

namespace Cartino\Http\Requests\AbandonedCart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbandonedCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:abandoned,recovered,lost',
            'recovery_email_sent_at' => 'nullable|date',
            'recovered_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be abandoned, recovered, or lost.',
            'recovery_email_sent_at.date' => 'Recovery email sent date must be a valid date.',
            'recovered_at.date' => 'Recovered date must be a valid date.',
        ];
    }
}
