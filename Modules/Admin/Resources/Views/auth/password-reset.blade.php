@extends('admin::layouts.auth')

@section('title', 'فراموشی رمز عبور')

@section('content')
<article
    class="auth-card w-full max-w-md p-6 sm:p-8 rounded-3xl border bg-surface-950/90 backdrop-blur-xl shadow-2xl animate-card-enter"
    role="form"
    aria-labelledby="reset-title"
>
    <!-- Logo -->
    <!-- Logo -->
    <div id="login-icon" class="flex justify-center mb-6">
        <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="animate-pulse-glow w-16 h-16 rounded-xl object-contain relative z-10" />
    </div>

    <!-- Header -->
    <header class="text-center mb-8">
        <h1 id="reset-title" class="text-2xl sm:text-3xl font-bold text-white mb-2">
            فراموشی رمز عبور
        </h1>
        <p class="text-sm sm:text-base text-slate-400 leading-relaxed px-2">
            ایمیل خود را وارد کنید تا کد بازیابی برایتان ارسال شود
        </p>
    </header>

    {{-- Password Reset Form --}}
    <livewire:admin.auth.password-reset-form />

    {{-- Back to Login --}}
    <footer class="mt-6 text-center">
        <a href="{{ route('admin.login') }}" class="text-sm text-primary-400 hover:text-primary-300 transition-colors duration-150">
            بازگشت به صفحه ورود
        </a>
    </footer>
</article>
@endsection
