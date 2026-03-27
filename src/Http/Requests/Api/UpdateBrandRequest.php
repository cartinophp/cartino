<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $brand = $this->route('brand');

        return $this->user()?->can('update', $brand) ?? false;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')->id ?? $this->route('brand');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', "unique:brands,slug,{$brandId}"],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
