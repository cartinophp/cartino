<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route(strtolower('Currency'));

        return $this->user()?->can('update', $item) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['sometimes', 'string', 'max:255']];
    }
}
