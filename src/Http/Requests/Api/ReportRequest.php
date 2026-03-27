<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'group_by' => ['nullable', Rule::in(['hour', 'day', 'week', 'month', 'year'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
