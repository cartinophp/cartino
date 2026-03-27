<?php

namespace Cartino\Http\Requests\AbandonedCart;

use Illuminate\Foundation\Http\FormRequest;

class SendRecoveryEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_template' => 'nullable|string|in:default,personalized,discount',
            'discount_code' => 'nullable|string|max:50',
            'custom_message' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'email_template.in' => 'Email template must be default, personalized, or discount.',
            'discount_code.max' => 'Discount code cannot exceed 50 characters.',
            'custom_message.max' => 'Custom message cannot exceed 1000 characters.',
        ];
    }
}
