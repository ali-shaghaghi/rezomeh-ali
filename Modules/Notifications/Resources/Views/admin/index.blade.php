@extends('adminpanel::layouts.master')

@section('title', 'اعلان‌ها')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">اعلان‌ها</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">اعلان‌های سیستم و فعالیت‌های اخیر</p>
        </div>
        <a href="{{ route('admin.analytics.index') }}" class="btn-outline !px-4 !py-2 !text-xs">داشبورد گزارش‌ها</a>
    </div>

    <livewire:notifications.list />
@endsection
