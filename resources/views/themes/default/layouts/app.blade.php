<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <!-- SEO Meta Tags -->
    @if(isset($seo))
        <meta name="description" content="{{ $seo['description'] ?? '' }}">
        <meta name="keywords" content="{{ $seo['keywords'] ?? '' }}">

        <!-- Open Graph -->
        <meta property="og:title" content="{{ $seo['og_title'] ?? $title ?? config('app.name') }}">
        <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? '' }}">
        <meta property="og:image" content="{{ $seo['og_image'] ?? '' }}">
        <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seo['twitter_title'] ?? $title ?? config('app.name') }}">
        <meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['description'] ?? '' }}">
        <meta name="twitter:image" content="{{ $seo['twitter_image'] ?? $seo['og_image'] ?? '' }}">
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/storefront.css', 'resources/js/storefront.js'])

    @stack('styles')
</head>
<body class="min-h-full bg-gray-50 font-sans antialiased">
    <div class="flex min-h-screen flex-col">
        <!-- Header -->
        <x-storefront::header />

        <!-- Main Content -->
        <main class="flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <x-storefront::footer />
    </div>

    <!-- Cart Sidebar (off-canvas) -->
    <x-storefront::cart-sidebar />

    <!-- Mobile Menu -->
    <x-storefront::mobile-menu />

    <!-- Flash Messages -->
    @if(session('success'))
        <x-storefront::notification type="success" :message="session('success')" />
    @endif

    @if(session('error'))
        <x-storefront::notification type="error" :message="session('error')" />
    @endif

    @stack('scripts')

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
