<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', Rule::in(['full_access', 'read_only', 'custom'])],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
