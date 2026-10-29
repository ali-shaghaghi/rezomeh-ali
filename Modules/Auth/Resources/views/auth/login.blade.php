@extends('auth::layouts.auth')

@section('title', __('auth.login'))

@section('content')
<article
    id="login-card"
    class="auth-card w-full max-w-md p-6 sm:p-8 rounded-3xl border bg-surface-950/90 backdrop-blur-xl shadow-2xl animate-card-enter"
    role="form"
    aria-labelledby="login-title"
>
    <div id="login-step-main">
        <!-- Logo -->
        <div id="login-icon" class="flex justify-center mb-6">
            <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="animate-pulse-glow w-16 h-16 rounded-xl object-contain relative z-10" />
        </div>

        <!-- Header -->
        <header class="text-center mb-8">
            <h1 id="login-title" class="text-2xl sm:text-3xl font-bold text-white mb-2">
                {{ __('auth.login_title') }}
            </h1>
            <p class="text-sm sm:text-base text-slate-400 leading-relaxed px-2">
                {{ __('auth.login_subtitle') }}
            </p>
        </header>

        <!-- Login Form -->
        <livewire:auth.login-form />

        <!-- Register Link -->
        <p class="mt-4 text-center text-sm text-slate-500">
            {{ __('auth.no_account') }}
            <a href="{{ route('auth.register') }}" class="text-primary-400 hover:text-primary-300 transition">
                {{ __('auth.register_now') }}
            </a>
        </p>
    </div>

    <!-- Success Step (hidden by default) -->
    <div id="login-step-success" class="hidden text-center" aria-live="polite">
        <div class="success-check-wrap mb-6">
            <div class="success-check-ring"></div>
            <div class="success-check-circle">
                <svg class="w-12 h-12" viewBox="0 0 52 52" aria-hidden="true">
                    <circle class="opacity-30" cx="26" cy="26" r="25" fill="none" stroke="#22c55e" stroke-width="2" />
                    <path class="success-check-path" fill="none" d="M14 27l7 7 16-16" />
                </svg>
            </div>
        </div>
        <h2 class="text-2xl sm:text-3xl font-bold text-emerald-400 mb-2">{{ __('auth.login_success') }}</h2>
        <p class="text-sm sm:text-base text-slate-400 mb-1">{{ __('auth.welcome') }}</p>
        <p class="text-xs text-emerald-500/80 mb-8">{{ __('auth.auth_success') }}</p>
        <div class="flex flex-col gap-3">
            @php
                $postLoginUrl = auth()->check()
                    ? route('user.dashboard')
                    : route('auth.login');
            @endphp
            <a href="{{ $postLoginUrl }}" class="btn-success w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white text-center">
                {{ __('auth.go_to_dashboard') }}
            </a>
        </div>
    </div>
</article>
@endsection
