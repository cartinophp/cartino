<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGlobalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $globalId = $this->route('global');

        return [
            'handle' => ['sometimes', 'string', 'max:255', "unique:globals,handle,{$globalId}", 'regex:/^[a-z0-9_]+$/'],
            'title' => ['sometimes', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
        ];
    }
}
