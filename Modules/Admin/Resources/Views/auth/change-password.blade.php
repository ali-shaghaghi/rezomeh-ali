@extends('admin::layouts.auth')

@section('title', 'تغییر رمز عبور')

@section('content')
<article
    class="auth-card w-full max-w-md p-6 sm:p-8 rounded-3xl border bg-surface-950/90 backdrop-blur-xl shadow-2xl animate-card-enter"
>
    <div class="text-center mb-6">
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-2xl bg-primary-500/10 border border-primary-500/30 flex items-center justify-center">
                <x-icon name="shield" size="8" class="text-primary-400" />
            </div>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">تغییر رمز عبور</h1>
        <p class="text-slate-400">رمز عبور خود را به‌روزرسانی کنید</p>
    </div>

    <livewire:admin.auth.change-password />

    <div class="mt-6 text-center">
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-slate-400 transition">
            بازگشت به داشبورد
        </a>
    </div>
</article>
@endsection