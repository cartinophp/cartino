<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', Rule::exists(User::class, 'id')],
        ];
    }
}
