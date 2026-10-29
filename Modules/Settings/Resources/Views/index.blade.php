@extends('adminpanel::layouts.master')

@section('title', 'تنظیمات')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold sm:text-3xl">تنظیمات سیستم</h2>
        <p class="mt-1 text-slate-500 dark:text-slate-400">پیکربندی سایت، پروفایل، امنیت، ایمیل، درگاه پرداخت و سرویس‌ها — جابه‌جایی بین بخش‌ها بدون بارگذاری مجدد صفحه</p>
    </div>

    @livewire('admin.settings.manager', ['initialTab' => $activeGroup], key('settings-manager'))
@endsection
