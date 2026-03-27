@props([
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'rows' => 3,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
])

<textarea
    name="{{ $name }}"
    placeholder="{{ $placeholder }}"
    rows="{{ $rows }}"
    {{ $required ? 'required' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    {{ $readonly ? 'readonly' : '' }}
    {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed']) }}
>{{ old($name, $value) }}</textarea>
