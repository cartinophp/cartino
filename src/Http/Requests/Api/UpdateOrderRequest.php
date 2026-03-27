<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $this->user()?->can('update', $order) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_email' => ['sometimes', 'email'],
            'status' => ['sometimes', 'string'],
            'payment_status' => ['sometimes', 'string'],
            'fulfillment_status' => ['sometimes', 'string'],
            'shipping_address' => ['sometimes', 'array'],
            'billing_address' => ['sometimes', 'array'],
            'notes' => ['nullable', 'string'],
            'shipped_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
        ];
    }
}
