<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Product;
use Cartino\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', Rule::exists(Supplier::class, 'id')],
            'po_number' => ['required', 'string', 'max:50', 'unique:purchase_orders,po_number'],
            'status' => ['required', 'in:draft,sent,confirmed,received,cancelled'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after:order_date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
