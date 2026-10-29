@extends('auth::layouts.auth')

@section('title', 'تایید هویت مدیران')

@section('content')
<article class="w-full max-w-md p-6 border shadow-2xl auth-card sm:p-8 rounded-3xl bg-surface-950/90 backdrop-blur-xl animate-card-enter">
    <!-- Logo -->
    <div class="flex justify-center mb-6">
        <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="relative z-10 object-contain w-16 h-16 animate-pulse-glow rounded-xl" />
    </div>

    <!-- Header -->
    <header class="mb-6 text-center">
        <h1 class="mb-2 text-2xl font-bold text-white sm:text-3xl">
            {{ $step === 'sms' ? 'کد تایید پیامکی' : 'ورود دو مرحله‌ای' }}
        </h1>
        <p class="px-2 text-sm leading-relaxed text-slate-400">
            {{ $step === 'sms' ? 'کد ۶ رقمی به شماره ' : 'کد ۶ رقمی به ایمیل ' }}
            <bdi class="font-medium text-primary-400">{{ $maskedContact }}</bdi>
        </p>
        <p class="mt-1 text-xs text-slate-500">
            ارسال شده است. کد را وارد کنید.
        </p>
    </header>

    @if ($errors->any())
        <div class="px-4 py-3 mb-5 text-sm border rounded-xl border-rose-500/40 bg-rose-500/10 text-rose-300 animate-shake" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="px-4 py-3 mb-5 text-sm border rounded-xl border-emerald-500/40 bg-emerald-500/10 text-emerald-300" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.verify.post', request()->route('token')) }}" class="space-y-5">
        @csrf

        <div>
            <label for="code" class="mb-1.5 block text-center text-sm font-medium text-slate-300">کد یکبارمصرف ۶ رقمی</label>
            <input
                id="code"
                type="text"
                name="code"
                value="{{ old('code') }}"
                dir="ltr"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                maxlength="6"
                required
                autofocus
                placeholder="••••••"
                class="otp-input mx-auto block w-full max-w-[260px] rounded-xl border border-slate-700 px-4 py-3.5 tracking-[0.5em]"
            />
        </div>

        @if ($devCode)
            <p class="text-xs text-center text-amber-400/90" dir="ltr">
                (حالت توسعه — کد ارسالی: {{ $devCode }})
            </p>
        @endif

        <button type="submit" class="btn-primary w-full rounded-xl py-3.5 text-base font-semibold text-white">
            تایید و ورود
        </button>
    </form>

    <!-- Resend — شمارش معکوس زنده با Alpine تا فعال‌شدن دکمه (قبلاً ثابت بود و نمی‌شمرد) -->
    <div class="mt-5 text-center"
         x-data="{ left: {{ (int) $cooldown }} }"
         x-init="if (left > 0) { let t = setInterval(() => { left--; if (left <= 0) clearInterval(t); }, 1000); }">
        <template x-if="left > 0">
            <span class="text-xs text-slate-500">
                ارسال مجدد کد تا <span class="font-semibold text-primary-400" x-text="left"></span> ثانیه دیگر
            </span>
        </template>
        <template x-if="left <= 0">
            <form method="POST" action="{{ route('admin.login.resend', request()->route('token')) }}" class="inline">
                @csrf
                <button type="submit" class="text-sm transition text-primary-400 hover:text-primary-300">
                    ارسال مجدد کد
                </button>
            </form>
        </template>
    </div>

    <p class="mt-6 text-xs text-center text-slate-500">
        <a href="{{ route('admin.login', request()->route('token')) }}" class="transition text-slate-400 hover:text-slate-200">بازگشت به صفحه ورود</a>
        &nbsp;|&nbsp;
        <a href="{{ route('auth.login') }}" class="transition text-slate-400 hover:text-slate-200">ورود کاربران</a>
    </p>
</article>
@endsection
