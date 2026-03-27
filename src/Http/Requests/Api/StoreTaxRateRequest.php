<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:tax_rates,code'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'type' => ['required', 'in:percentage,fixed'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'is_enabled' => ['boolean'],
        ];
    }
}
