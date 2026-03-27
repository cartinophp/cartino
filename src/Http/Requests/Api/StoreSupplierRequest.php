<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Supplier::class) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }
}
