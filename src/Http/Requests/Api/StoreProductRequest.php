<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Brand;
use Cartino\Models\Product;
use Cartino\Models\ProductType;
use Cartino\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Product::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'handle' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'product_type' => ['required', 'in:physical,digital,service'],
            'brand_id' => ['nullable', Rule::exists(Brand::class, 'id')],
            'product_type_id' => ['nullable', Rule::exists(ProductType::class, 'id')],
            'site_id' => ['required', Rule::exists(Site::class, 'id')],
            'status' => ['required', 'in:draft,published,archived'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'options' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'requires_selling_plan' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Il titolo del prodotto è obbligatorio',
            'slug.required' => 'Lo slug è obbligatorio',
            'slug.unique' => 'Questo slug è già utilizzato',
            'product_type.required' => 'Il tipo di prodotto è obbligatorio',
            'product_type.in' => 'Il tipo di prodotto deve essere physical, digital o service',
            'site_id.required' => 'Il sito è obbligatorio',
            'site_id.exists' => 'Il sito selezionato non esiste',
            'status.required' => 'Lo stato è obbligatorio',
            'status.in' => 'Lo stato deve essere draft, published o archived',
        ];
    }
}
