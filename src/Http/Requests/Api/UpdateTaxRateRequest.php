<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $taxRateId = $this->route('tax_rate');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', "unique:tax_rates,code,{$taxRateId}"],
            'rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'type' => ['sometimes', 'in:percentage,fixed'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'is_enabled' => ['boolean'],
        ];
    }
}
