<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $channelId = $this->route('channel')?->id;

        return [
            'site_id' => ['required|', Rule::exists(Site::class, 'id')],
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('channels', 'slug')->ignore($channelId),
            ],
            'description' => 'nullable|string',
            'type' => [
                'required',
                Rule::in(['web', 'mobile', 'pos', 'marketplace', 'b2b_portal', 'social', 'api']),
            ],
            'url' => 'nullable|url|max:255',
            'is_default' => 'boolean',
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'locales' => 'nullable|array',
            'locales.*' => 'string|max:10',
            'currencies' => 'nullable|array',
            'currencies.*' => 'string|size:3', // ISO 4217
            'settings' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required' => 'The site is required.',
            'site_id.exists' => 'The selected site does not exist.',
            'name.required' => 'The channel name is required.',
            'slug.required' => 'The channel slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes and underscores.',
            'type.required' => 'The channel type is required.',
            'type.in' => 'Invalid channel type selected.',
            'currencies.*.size' => 'Currency codes must be 3-letter ISO codes (e.g., EUR, USD).',
        ];
    }
}
