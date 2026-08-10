<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="@yield('meta-description', 'پنل ادمین')" />
    <title>@yield('title', 'پنل ادمین') | {{ config('app.name', 'AliShaghaghi') }}</title>
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml" />

    @include('admin.partials.styles')

    @livewireStyles
</head>
<body class="bg-surface-50 text-slate-800 transition-colors duration-500 dark:bg-surface-950 dark:text-slate-200">

    @include('admin.partials.sidebar')

    @include('admin.partials.header')

    <main class="flex-1 p-4 sm:p-6 lg:p-8">
        @if(session('success'))
            <x-admin-alert type="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-admin-alert type="error" message="{{ session('error') }}" />
        @endif
        @if(session('warning'))
            <x-admin-alert type="warning" message="{{ session('warning') }}" />
        @endif

        @yield('content')
    </main>

    @include('admin.partials.footer')
    @include('admin.partials.scripts')

    @livewireScripts
</body>
</html>