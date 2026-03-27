<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreGlobalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'handle' => ['required', 'string', 'max:255', 'unique:globals,handle', 'regex:/^[a-z0-9_]+$/'],
            'title' => ['required', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
        ];
    }
}
