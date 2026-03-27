<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255|unique:collections,name',
            'description' => 'nullable|string',
            'type' => 'required|in:manual,automatic',
            'conditions' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'string|in:active,inactive',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome della collezione è obbligatorio.',
            'name.unique' => 'Questo nome della collezione è già in uso.',
            'name.max' => 'Il nome della collezione non può superare i 255 caratteri.',
            'type.required' => 'Il tipo di collezione è obbligatorio.',
            'type.in' => 'Il tipo di collezione deve essere manual o automatic.',
            'sort_order.min' => 'L\'ordine di visualizzazione deve essere maggiore o uguale a 0.',
            'published_at.date' => 'La data di pubblicazione deve essere una data valida.',
            'seo_title.max' => 'Il titolo SEO non può superare i 255 caratteri.',
            'seo_description.max' => 'La descrizione SEO non può superare i 500 caratteri.',
        ];
    }
}
