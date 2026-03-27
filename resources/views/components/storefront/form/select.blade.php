@props([
    'name' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
])

<select
    name="{{ $name }}"
    {{ $required ? 'required' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed']) }}
>
    {{ $slot }}
</select>
