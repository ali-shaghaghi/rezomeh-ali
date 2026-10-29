@extends('adminpanel::layouts.master')

@section('title', 'زمان‌بندی مشاوران')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">زمان‌بندی مشاوران</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">ساعت کاری هفتگی هر مشاور — مثل نوبت‌دهی مطب</p>
        </div>
        <a href="{{ route('admin.consultations.consultants') }}" class="btn-outline !px-4 !py-2 !text-xs">مشاوران</a>
    </div>

    <livewire:consultation.admin.schedules-manager />
@endsection