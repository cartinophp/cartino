<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['full_access', 'read_only', 'custom'])],
            'permissions' => ['required_if:type,custom', 'array'],
            'permissions.*' => ['string'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'is_active' => ['boolean'],
        ];
    }
}
