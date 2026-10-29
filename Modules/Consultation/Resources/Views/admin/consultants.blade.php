@extends('adminpanel::layouts.master')

@section('title', 'مدیریت مشاوران')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">مشاوران</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">مدیریت پروفایل مشاوران</p>
        </div>
        <a href="{{ route('admin.consultations.schedules') }}" class="btn-outline !px-4 !py-2 !text-xs">زمان‌بندی‌ها</a>
    </div>

    <livewire:consultation.admin.consultants-manager />
@endsection