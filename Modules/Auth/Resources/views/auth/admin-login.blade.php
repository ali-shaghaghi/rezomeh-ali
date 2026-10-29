@extends('auth::layouts.auth')

@section('title', 'ورود مدیران')

@section('content')
<article
    class="auth-card w-full max-w-md p-6 sm:p-8 rounded-3xl border bg-surface-950/90 backdrop-blur-xl shadow-2xl animate-card-enter"
    role="form"
    aria-labelledby="admin-login-title"
>
    <!-- Logo -->
    <div class="flex justify-center mb-6">
        <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="animate-pulse-glow w-16 h-16 rounded-xl object-contain relative z-10" />
    </div>

    <!-- Header -->
    <header class="text-center mb-8">
        <h1 id="admin-login-title" class="text-2xl sm:text-3xl font-bold text-white mb-2">ورود مدیران</h1>
        <p class="text-sm sm:text-base text-slate-400 leading-relaxed px-2">
            ورود کادر فنی با شماره موبایل + رمز عبور و <span class="text-primary-400 font-semibold">کد پیامکی</span>
        </p>
    </header>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300 animate-shake" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.post.login', request()->route('token')) }}" class="space-y-5">
        @csrf

        <!-- Phone -->
        <div>
            <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-300">شماره موبایل</label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone', $lockedAdmin?->phone) }}"
                dir="ltr"
                inputmode="numeric"
                autocomplete="username"
                required
                autofocus
                placeholder="09xxxxxxxxx"
                class="w-full rounded-xl border border-slate-700 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-primary-400 focus:ring-2 focus:ring-primary-400/30"
            />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-300">رمز عبور</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
                placeholder="••••••••"
                class="w-full rounded-xl border border-slate-700 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-primary-400 focus:ring-2 focus:ring-primary-400/30"
            />
        </div>

        <!-- Remember -->
        <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-600 bg-white/5 text-primary-500 focus:ring-primary-400/40" />
            ورود من را به خاطر بسپار
        </label>

        <button type="submit" class="btn-primary w-full rounded-xl py-3.5 text-base font-semibold text-white">
            ارسال کد پیامکی
        </button>
    </form>

    @if ($lockedAdmin)
        <p class="mt-4 text-center text-xs text-slate-400">
            ورود از مسیر اختصاصی <span class="font-semibold text-slate-300" dir="ltr">{{ $lockedAdmin->name }}</span>
            — فقط همین حساب اجازه ورود از این مسیر را دارد.
        </p>
    @endif

    <p class="mt-6 text-center text-xs text-slate-500 leading-relaxed">
        ورود کاربران عادی از
        <a href="{{ route('auth.login') }}" class="text-primary-400 hover:text-primary-300 transition">صفحه ورود کاربران</a>
        انجام می‌شود.
    </p>
</article>
@endsection
