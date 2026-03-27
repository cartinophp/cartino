<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $site = $this->route('site');

        return $this->user()?->can('update', $site) ?? false;
    }

    public function rules(): array
    {
        $siteId = $this->route('site')->id ?? $this->route('site');

        return [
            'handle' => ['sometimes', 'string', 'max:255', "unique:sites,handle,{$siteId}"],
            'name' => ['sometimes', 'string', 'max:255'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['sometimes', Rule::enum(Status::class)],
        ];
    }
}
