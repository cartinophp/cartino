@props([
    'href' => '#main-content',
    'text' => 'Skip to main content',
])

<a
    href="{{ $href }}"
    class="skip-link"
    {{ $attributes }}
>
    {{ $text }}
</a>

<style>
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    z-index: 100;
    padding: 8px 16px;
    background: #4f46e5;
    color: white;
    text-decoration: none;
    border-radius: 0 0 4px 0;
    font-weight: 600;
}

.skip-link:focus {
    top: 0;
}
</style>
