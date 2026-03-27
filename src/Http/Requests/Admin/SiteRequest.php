<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteId = $this->route('site')?->id;

        return [
            'handle' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('sites', 'handle')->ignore($siteId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|url|max:255',
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('sites', 'domain')->ignore($siteId),
            ],
            'domains' => 'nullable|array',
            'domains.*' => 'string|max:255',
            'locale' => 'required|string|max:10',
            'lang' => 'required|string|max:5',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2', // ISO 3166-1 alpha-2
            'default_currency' => 'required|string|size:3', // ISO 4217
            'tax_included_in_prices' => 'boolean',
            'tax_region' => 'nullable|string|max:255',
            'priority' => 'integer|min:0|max:9999',
            'is_default' => 'boolean',
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'order' => 'integer|min:0',
            'published_at' => 'nullable|date',
            'unpublished_at' => 'nullable|date|after:published_at',
            'attributes' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'handle.required' => 'The site handle is required.',
            'handle.unique' => 'This handle is already in use.',
            'handle.alpha_dash' => 'The handle may only contain letters, numbers, dashes and underscores.',
            'name.required' => 'The site name is required.',
            'url.required' => 'The site URL is required.',
            'url.url' => 'Please provide a valid URL.',
            'domain.unique' => 'This domain is already in use.',
            'locale.required' => 'The default locale is required.',
            'default_currency.required' => 'The default currency is required.',
            'default_currency.size' => 'Currency must be a 3-letter ISO code (e.g., EUR, USD).',
            'countries.*.size' => 'Country codes must be 2-letter ISO codes (e.g., IT, US).',
            'unpublished_at.after' => 'Unpublish date must be after publish date.',
        ];
    }
}
