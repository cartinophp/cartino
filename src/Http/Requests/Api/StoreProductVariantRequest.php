<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Enums\Status;
use Cartino\Models\Product;
use Cartino\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'site_id' => ['required', 'integer', Rule::exists(Site::class, 'id')],
            'sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'requires_shipping' => ['boolean'],
            'taxable' => ['boolean'],
            'status' => ['required', Rule::enum(Status::class)],
        ];
    }
}
