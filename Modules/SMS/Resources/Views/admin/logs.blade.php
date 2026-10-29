@extends('adminpanel::layouts.master')

@section('title', 'لاگ پیامک‌ها')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">لاگ پیامک‌ها</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تاریخچه ارسال پیامک‌ها</p>
        </div>
        <a href="{{ route('admin.sms.index') }}" class="btn-outline !px-4 !py-2 !text-xs">تنظیمات پیامک</a>
    </div>

    <livewire:sms.logs />
@endsection