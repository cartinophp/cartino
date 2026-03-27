@props([
    'priority' => 'polite', // polite | assertive
    'atomic' => true,
])

<div
    role="status"
    aria-live="{{ $priority }}"
    aria-atomic="{{ $atomic ? 'true' : 'false' }}"
    class="sr-only"
    {{ $attributes }}
>
    {{ $slot }}
</div>
