<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Enums\TransactionStatus;
use Cartino\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', Rule::exists(Order::class, 'id')],
            'transaction_id' => ['required', 'string', 'max:255', 'unique:transactions,transaction_id'],
            'gateway' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:authorization,capture,sale,refund,void'],
            'status' => ['required', Rule::enum(TransactionStatus::class)],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'size:3'],
            'gateway_response' => ['nullable', 'array'],
        ];
    }
}
