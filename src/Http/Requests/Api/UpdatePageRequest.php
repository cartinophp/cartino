<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pageId = $this->route('page');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', "unique:pages,slug,{$pageId}"],
            'content' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'template' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
