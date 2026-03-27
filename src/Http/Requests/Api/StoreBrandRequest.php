<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Enums\Status;
use Cartino\Models\Brand;
use Cartino\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Brand::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:brands,slug'],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', Rule::enum(Status::class)],
            'is_featured' => ['nullable', 'boolean'],
            'site_id' => ['nullable', Rule::exists(Site::class, 'id')],
        ];
    }
}
