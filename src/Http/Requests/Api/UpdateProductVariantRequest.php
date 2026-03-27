<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variantId = $this->route('variant');

        return [
            'sku' => ['sometimes', 'string', 'max:100', "unique:product_variants,sku,{$variantId}"],
            'title' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'requires_shipping' => ['boolean'],
            'taxable' => ['boolean'],
            'status' => ['sometimes', Rule::enum(Status::class)],
        ];
    }
}
