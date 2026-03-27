<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;

class StoreChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Channel::class) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }
}
