<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $countryId = $this->route('country');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'size:2', "unique:countries,code,{$countryId}"],
            'iso3' => ['nullable', 'string', 'size:3'],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'is_enabled' => ['boolean'],
        ];
    }
}
