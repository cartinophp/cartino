<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Enums\Status;
use Cartino\Models\Courier;
use Cartino\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Courier::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:couriers,slug'],
            'code' => ['required', 'string', 'max:255', 'unique:couriers,code'],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
            'delivery_time_min' => ['nullable', 'integer', 'min:0'],
            'delivery_time_max' => ['nullable', 'integer', 'min:0', 'gte:delivery_time_min'],
            'status' => ['nullable', Rule::enum(Status::class)],
            'is_enabled' => ['nullable', 'boolean'],
            'site_id' => ['nullable', Rule::exists(Site::class, 'id')],
            'seo' => ['nullable', 'array'],
            'meta' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_time_max.gte' => 'Il tempo di consegna massimo deve essere maggiore o uguale al tempo minimo',
        ];
    }
}
