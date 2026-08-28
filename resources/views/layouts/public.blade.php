<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title . ' — ' . __('common.brand_name') : __('common.brand_name') . ' — ' . __('common.tagline') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-brand-ivory text-brand-text font-sans antialiased selection:bg-brand-gold-light selection:text-brand-primary">
    <!-- Accessible Skip Link (Targeting single primary #main-content) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-5 focus:py-3 focus:bg-brand-primary focus:text-white focus:font-semibold focus:rounded-lg focus:shadow-xl focus:ring-2 focus:ring-brand-gold focus:outline-none transition">
        {{ __('navigation.skip_to_content') }}
    </a>

    <!-- Global Public Header -->
    <x-public.header />

    <!-- Primary Main Landmark -->
    <main id="main-content" class="flex-1 focus:outline-none" tabindex="-1">
        {{ $slot }}
    </main>

    <!-- Global Public Footer -->
    <x-public.footer />
</body>
</html>
