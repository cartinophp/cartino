<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Control Panel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Cartino Global Configuration -->
    <script>
        window.CartinoConfig = {
            locale: @json(app()->getLocale()),
            translations: @json($translations ?? []),
            csrf_token: @json(csrf_token()),
            app_url: @json(config('app.url')),
            timezone: @json(config('app.timezone')),
            currency: @json(config('cartino.currency', 'EUR')),
        };
    </script>

    @if(\Cartino\Support\Asset::isBuilt())
        {!! \Cartino\Support\Asset::styles() !!}
        {!! \Cartino\Support\Asset::scripts() !!}
    @else
        {{-- In development mode, Vite dev server must be running in the sandbox app (cartino-test) --}}
        {{-- The sandbox vite.config.js includes '../cartino/resources/js/app.js' as input --}}
        @vite(['../cartino/resources/js/app.js'])
    @endif
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
