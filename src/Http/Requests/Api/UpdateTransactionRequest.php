<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:pending,processing,success,failed'],
            'gateway_response' => ['nullable', 'array'],
            'processed_at' => ['nullable', 'date'],
        ];
    }
}
