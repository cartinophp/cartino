<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Catalog;
use Cartino\Models\Channel;
use Cartino\Models\Market;
use Cartino\Models\ProductVariant;
use Cartino\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Variant identification
            'variant_id' => ['sometimes', 'integer', Rule::exists(ProductVariant::class, 'id')],
            'sku' => ['sometimes', 'string', Rule::exists(ProductVariant::class, 'sku')],
            // Context (optional, will use session if not provided)
            'market_id' => ['sometimes', 'integer', Rule::exists(Market::class, 'id')],
            'market_code' => ['sometimes', 'string', Rule::exists(Market::class, 'code')],
            'site_id' => ['sometimes', 'integer', Rule::exists(Site::class, 'id')],
            'channel_id' => ['sometimes', 'integer', Rule::exists(Channel::class, 'id')],
            'catalog_id' => ['sometimes', 'integer', Rule::exists(Catalog::class, 'id')],
            'currency' => ['sometimes', 'string', 'size:3'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:10000'],
            // Options
            'include_tiers' => ['sometimes', 'boolean'],
            'include_context' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Default quantity to 1
        if (! isset($validated['quantity'])) {
            $validated['quantity'] = 1;
        }

        return $key ? ($validated[$key] ?? $default) : $validated;
    }
}
