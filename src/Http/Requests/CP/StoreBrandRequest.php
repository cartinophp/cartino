<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\CP;

use Cartino\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create_brands');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(Brand::class, 'slug'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del brand è obbligatorio.',
            'slug.required' => 'Lo slug è obbligatorio.',
            'slug.unique' => 'Questo slug è già in uso.',
            'slug.regex' => 'Lo slug deve contenere solo lettere minuscole, numeri e trattini.',
            'status.required' => 'Lo status è obbligatorio.',
            'status.in' => 'Lo status deve essere attivo o inattivo.',
            'website.url' => 'Il sito web deve essere un URL valido.',
            'image.image' => 'Il file deve essere un\'immagine.',
            'image.max' => 'L\'immagine non può superare i 2MB.',
        ];
    }

    public function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => str($this->name)->slug()->toString(),
            ]);
        }
    }
}
