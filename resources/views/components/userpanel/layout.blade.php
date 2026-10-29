<!DOCTYPE html>
<html lang="{{ current_locale() }}" dir="{{ locale_direction() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <link rel="icon" href="{{ site_favicon_url() }}">

    {{-- سئوی داینامیک (#ماژول سئو) — پنل کاربر noindex است --}}
    <x-seo::head />

    @include('partials.theme-init')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
    {{ $head ?? '' }}
</head>
<body class="font-sans text-slate-800 dark:text-slate-100 transition-all duration-300 min-h-screen">
    <div class="bg-mesh" aria-hidden="true"></div>
    <div class="bg-orbs" aria-hidden="true">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    @include('partials.splash-screen')

    @include('public::partials.header')

    <main class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-20 overflow-x-clip">
        {{ $slot }}
    </main>

    @include('public::partials.footer')

    {{-- ویجت شناور تماس — روی همه صفحات پنل کاربر --}}
    @livewire('contact.floating-widget')

    @stack('scripts')
</body>
</html>
