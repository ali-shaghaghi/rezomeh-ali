@extends('admin::layouts.auth')

@section('title', 'احراز هویت دو مرحله‌ای')

@section('content')
<article
    id="otp-card"
    class="auth-card w-full max-w-md p-6 sm:p-8 rounded-3xl border bg-surface-950/90 backdrop-blur-xl shadow-2xl animate-card-enter"
    role="form"
    aria-labelledby="otp-title"
>
    <div id="otp-step-login">
        <!-- Logo -->
        <div id="login-icon" class="flex justify-center mb-6">
            <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="animate-pulse-glow w-16 h-16 rounded-xl object-contain relative z-10" />
        </div>

        <!-- Header -->
        <header class="text-center mb-8">
            <h1 id="otp-title" class="text-2xl sm:text-3xl font-bold text-white mb-2">
                احراز هویت دو مرحله‌ای
            </h1>
            <p class="text-sm sm:text-base text-slate-400 leading-relaxed px-2">
                کد تایید ۶ رقمی به شماره شما ارسال شد.
            </p>
            <p class="text-xs text-slate-500 mt-2 tabular-nums" dir="ltr">
                {{ $maskedPhone ?? '***' }}
            </p>
        </header>

        {{-- OTP Verification Form --}}
        <livewire:admin.auth.two-factor-form />
    </div>

    <!-- Success Step (hidden by default) -->
    <div id="otp-step-success" class="hidden text-center" aria-live="polite">
        <div class="success-check-wrap mb-6">
            <div class="success-check-ring"></div>
            <div class="success-check-circle">
                <svg class="w-12 h-12" viewBox="0 0 52 52" aria-hidden="true">
                    <circle class="opacity-30" cx="26" cy="26" r="25" fill="none" stroke="#22c55e" stroke-width="2" />
                    <path class="success-check-path" fill="none" d="M14 27l7 7 16-16" />
                </svg>
            </div>
        </div>
        <h2 class="text-2xl sm:text-3xl font-bold text-emerald-400 mb-2">ورود موفق!</h2>
        <p class="text-sm sm:text-base text-slate-400 mb-1">به پنل ادمین خوش آمدید</p>
        <p class="text-xs text-emerald-500/80 mb-8">احراز هویت با موفقیت انجام شد</p>
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn-success w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white text-center">
                ورود به داشبورد
            </a>
        </div>
    </div>
</article>
@endsection
