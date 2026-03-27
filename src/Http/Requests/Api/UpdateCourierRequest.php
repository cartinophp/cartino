<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $courier = $this->route('courier');

        return $this->user()?->can('update', $courier) ?? false;
    }

    public function rules(): array
    {
        $courierId = $this->route('courier')->id ?? $this->route('courier');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', "unique:couriers,slug,{$courierId}"],
            'code' => ['sometimes', 'string', 'max:255', "unique:couriers,code,{$courierId}"],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
            'delivery_time_min' => ['nullable', 'integer', 'min:0'],
            'delivery_time_max' => ['nullable', 'integer', 'min:0', 'gte:delivery_time_min'],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'is_enabled' => ['nullable', 'boolean'],
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
