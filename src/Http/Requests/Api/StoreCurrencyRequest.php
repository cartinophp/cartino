<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Currency::class) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }
}
