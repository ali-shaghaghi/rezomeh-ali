@extends('auth::layouts.auth')

@section('title', 'تایید ایمیل')

@section('content')
<article
    id="email-verify-card"
    class="auth-card w-full max-w-md p-6 sm:p-8 rounded-3xl border bg-surface-950/90 backdrop-blur-xl shadow-2xl animate-card-enter"
    role="status"
    aria-labelledby="email-verify-title"
>
    <!-- Logo -->
    <div class="flex justify-center mb-6">
        <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="animate-pulse-glow w-16 h-16 rounded-xl object-contain relative z-10" />
    </div>

    <!-- Header -->
    <header class="text-center mb-8">
        <h1 id="email-verify-title" class="text-2xl sm:text-3xl font-bold text-white mb-2">
            تایید ایمیل
        </h1>
        <p class="text-sm sm:text-base text-slate-400 leading-relaxed px-2">
            لینک تایید به ایمیل شما ارسال شد؛ لطفاً صندوق ورودی خود را بررسی کنید.
        </p>
        <p class="text-xs text-slate-500 mt-2 tabular-nums" dir="ltr">
            {{ $maskedEmail ?? '***' }}
        </p>
    </header>

    @if(session('verification-link-sent'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm text-center">
            لینک تایید جدید ارسال شد.
        </div>
    @endif

    <!-- Actions -->
    <div class="flex flex-col gap-3">
        @if(auth()->check())
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white text-center transition-all duration-150">
                    ارسال مجدد لینک تایید
                </button>
            </form>

            <a href="{{ route('user.dashboard') }}"
               class="w-full py-3.5 px-4 rounded-xl text-base font-semibold text-center border border-slate-700 text-slate-300 hover:bg-white/5 transition-all duration-150">
                فعلاً ادامه می‌دهم — رفتن به داشبورد
            </a>
        @else
            <a href="{{ route('auth.login') }}"
               class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white text-center">
                ورود به حساب کاربری
            </a>
        @endif
    </div>

    <footer class="mt-6 text-center">
        <p class="text-xs text-slate-500">
            لینک تایید تا ۶۰ دقیقه معتبر است. اگر ایمیل را دریافت نکردید، پوشه Spam را بررسی کنید.
        </p>
    </footer>
</article>
@endsection
