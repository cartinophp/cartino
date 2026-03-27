<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => ['sometimes', 'required'],
            'type' => ['nullable', 'string', 'max:50'],
            'group' => ['nullable', 'string', 'max:100'],
            'is_public' => ['boolean'],
        ];
    }
}
