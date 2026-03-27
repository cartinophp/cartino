<?php

namespace Cartino\Http\Requests\StockNotification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:pending,notified,cancelled',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'preferred_method' => 'required|string|in:email,sms,both',
            'notified_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be pending, notified, or cancelled.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'preferred_method.required' => 'Preferred notification method is required.',
            'preferred_method.in' => 'Preferred method must be email, sms, or both.',
            'notified_at.date' => 'Notified date must be a valid date.',
        ];
    }
}
