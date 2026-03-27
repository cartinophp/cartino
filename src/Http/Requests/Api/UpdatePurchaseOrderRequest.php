<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:draft,sent,confirmed,received,cancelled'],
            'expected_delivery_date' => ['nullable', 'date'],
            'received_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
