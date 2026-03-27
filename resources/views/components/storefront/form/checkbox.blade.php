@props([
    'name' => '',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
])

<input
    type="checkbox"
    name="{{ $name }}"
    value="{{ $value }}"
    {{ $checked || old($name) ? 'checked' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed']) }}
>
