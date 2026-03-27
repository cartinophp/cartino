<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\CP;

use Cartino\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update_collections');
    }

    public function rules(): array
    {
        $collectionId = $this->route('collection')?->id ?? $this->route('collection');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(Category::class, 'slug')->ignore($collectionId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(['published', 'draft'])],
            'collection_type' => ['required', 'string', Rule::in(['manual', 'smart'])],
            'conditions' => ['nullable', 'array'],
            'conditions.*.field' => ['required_with:conditions', 'string'],
            'conditions.*.operator' => ['required_with:conditions', 'string'],
            'conditions.*.value' => ['required_with:conditions'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Il titolo della collezione è obbligatorio.',
            'slug.required' => 'Lo slug è obbligatorio.',
            'slug.unique' => 'Questo slug è già in uso.',
            'slug.regex' => 'Lo slug deve contenere solo lettere minuscole, numeri e trattini.',
            'status.required' => 'Lo status è obbligatorio.',
            'status.in' => 'Lo status deve essere pubblicato o bozza.',
            'collection_type.required' => 'Il tipo di collezione è obbligatorio.',
            'collection_type.in' => 'Il tipo deve essere manuale o intelligente.',
            'image.image' => 'Il file deve essere un\'immagine.',
            'image.max' => 'L\'immagine non può superare i 2MB.',
        ];
    }

    public function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('title')) {
            $this->merge([
                'slug' => str($this->title)->slug()->toString(),
            ]);
        }
    }
}
