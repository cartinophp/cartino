<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Menu::class) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }
}
