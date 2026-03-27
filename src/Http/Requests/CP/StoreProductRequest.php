<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\CP;

use Cartino\Models\Brand;
use Cartino\Models\Collection;
use Cartino\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'barcode' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:simple,variable,digital,subscription'],
            'status' => ['required', 'string', 'in:published,draft,archived'],
            'featured' => ['boolean'],
            // Pricing
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0', 'gt:price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            // Inventory
            'track_inventory' => ['boolean'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'allow_backorder' => ['boolean'],
            // Physical properties
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'requires_shipping' => ['boolean'],
            // Relationships
            'brand_id' => ['nullable', Rule::exists(Brand::class, 'id')],
            'collection_ids' => ['nullable', 'array'],
            'collection_ids.*' => [Rule::exists(Collection::class, 'id')],
            // SEO
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            // Media
            'media' => ['nullable', 'array'],
            'media.*.id' => ['nullable', Rule::exists(Media::class, 'id')],
            'media.*.alt' => ['nullable', 'string', 'max:255'],
            'media.*.position' => ['nullable', 'integer', 'min:0'],
            // Variants (for variable products)
            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.attributes' => ['nullable', 'array'],
            // Additional fields
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'product_type' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del prodotto è obbligatorio.',
            'slug.required' => 'Lo slug è obbligatorio.',
            'slug.unique' => 'Questo slug è già in uso.',
            'slug.regex' => 'Lo slug può contenere solo lettere minuscole, numeri e trattini.',
            'sku.unique' => 'Questo SKU è già in uso.',
            'price.required' => 'Il prezzo è obbligatorio.',
            'price.numeric' => 'Il prezzo deve essere un numero.',
            'price.min' => 'Il prezzo deve essere maggiore o uguale a 0.',
            'compare_price.gt' => 'Il prezzo di confronto deve essere maggiore del prezzo normale.',
            'type.required' => 'Il tipo di prodotto è obbligatorio.',
            'type.in' => 'Il tipo di prodotto selezionato non è valido.',
            'status.required' => 'Lo status è obbligatorio.',
            'status.in' => 'Lo status selezionato non è valido.',
            'brand_id.exists' => 'Il brand selezionato non esiste.',
            'collection_ids.*.exists' => 'Una delle collezioni selezionate non esiste.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'seo_title' => 'titolo SEO',
            'seo_description' => 'descrizione SEO',
            'short_description' => 'descrizione breve',
            'compare_price' => 'prezzo di confronto',
            'cost_price' => 'prezzo di costo',
            'track_inventory' => 'traccia inventario',
            'stock_quantity' => 'quantità in stock',
            'low_stock_threshold' => 'soglia stock basso',
            'allow_backorder' => 'consenti ordini in giacenza',
            'requires_shipping' => 'richiede spedizione',
            'brand_id' => 'brand',
            'collection_ids' => 'collezioni',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'featured' => $this->boolean('featured'),
            'track_inventory' => $this->boolean('track_inventory'),
            'allow_backorder' => $this->boolean('allow_backorder'),
            'requires_shipping' => $this->boolean('requires_shipping'),
            'status' => $this->input('status', 'draft'),
            'type' => $this->input('type', 'simple'),
        ]);

        // Generate slug from name if not provided
        if (! $this->input('slug') && $this->input('name')) {
            $this->merge([
                'slug' => Str::slug($this->input('name')),
            ]);
        }
    }
}
