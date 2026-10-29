@extends('auth::layouts.auth')

@section('title', __('auth.register'))

@section('content')
<article
    id="register-card"
    class="auth-card w-full max-w-md p-6 sm:p-8 rounded-3xl border bg-surface-950/90 backdrop-blur-xl shadow-2xl animate-card-enter"
    role="form"
    aria-labelledby="register-title"
>
    <!-- Logo -->
    <div class="flex justify-center mb-6">
        <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="animate-pulse-glow w-16 h-16 rounded-xl object-contain relative z-10" />
    </div>

    <!-- Header -->
    <header class="text-center mb-8">
        <h1 id="register-title" class="text-2xl sm:text-3xl font-bold text-white mb-2">
            {{ __('auth.register_title') }}
        </h1>
        <p class="text-sm sm:text-base text-slate-400 leading-relaxed px-2">
            {{ __('auth.register_subtitle') }}
        </p>
    </header>

    <!-- Register Form -->
    <form method="POST" action="{{ route('auth.register') }}" novalidate>
        @csrf
        <fieldset class="border-0 p-0 m-0 min-w-0">
            <legend class="sr-only">{{ __('auth.register') }}</legend>

            {{-- Name --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-slate-300 mb-2">{{ __('auth.name') }}</label>
                <div class="gradient-border-wrap">
                    <div class="input-inner relative flex items-center">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            placeholder="{{ __('auth.name_placeholder') }}"
                            class="w-full bg-transparent py-3 pr-11 pl-4 text-white placeholder-slate-500 outline-none"
                        />
                    </div>
                </div>
                @error('name')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-slate-300 mb-2">{{ __('auth.email') }}</label>
                <div class="gradient-border-wrap">
                    <div class="input-inner relative flex items-center">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            dir="ltr"
                            placeholder="email@example.com"
                            class="w-full bg-transparent py-3 pr-11 pl-4 text-white placeholder-slate-500 outline-none"
                        />
                    </div>
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">{{ __('auth.password') }}</label>
                <div class="gradient-border-wrap">
                    <div class="input-inner relative flex items-center">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            dir="ltr"
                            placeholder="••••••••"
                            class="w-full bg-transparent py-3 pr-11 pl-4 text-white placeholder-slate-500 outline-none"
                        />
                    </div>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Confirmation --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">{{ __('auth.confirm_password') }}</label>
                <div class="gradient-border-wrap">
                    <div class="input-inner relative flex items-center">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            dir="ltr"
                            placeholder="••••••••"
                            class="w-full bg-transparent py-3 pr-11 pl-4 text-white placeholder-slate-500 outline-none"
                        />
                    </div>
                </div>
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white flex items-center justify-center gap-2 transition-all duration-150"
            >
                <span>{{ __('auth.register') }}</span>
            </button>
        </fieldset>
    </form>

    <!-- Footer -->
    <footer class="mt-6 text-center">
        <p class="text-sm text-slate-500">
            {{ __('auth.have_account') }}
            <a href="{{ route('auth.login') }}" class="text-primary-400 hover:text-primary-300 transition">
                {{ __('auth.login_here') }}
            </a>
        </p>
    </footer>
</article>
@endsection