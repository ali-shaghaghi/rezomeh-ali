@extends('adminpanel::layouts.master')

@section('title', 'گالری تصاویر')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="section-badge !inline-flex !mb-2">مدیریت محتوا</div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">گالری تصاویر</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                همهٔ تصاویر سایت از همین‌جا آپلود و مدیریت می‌شوند؛ در وبلاگ و محصولات از دکمهٔ «گالری» در ویرایشگر متن انتخاب می‌شوند.
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
           class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
            بازگشت به داشبورد
        </a>
    </div>

    @livewire('admin.asset-grid')
@endsection
