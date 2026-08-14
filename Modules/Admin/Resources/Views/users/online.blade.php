@extends('admin::layouts.master')

@section('title', 'کاربران آنلاین')

@section('content')
    <!-- Title -->
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">کاربران آنلاین</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $onlineUsers->count() }} کاربر آنلاین</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
            بازگشت
        </a>
    </div>

    <!-- Online Users Grid -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($onlineUsers as $activity)
            <div class="chart-card flex items-center gap-4">
                <div class="relative">
                    <img src="{{ user_avatar_url($activity->user) }}" alt="" class="h-12 w-12 rounded-xl object-cover" />
                    <span class="absolute -bottom-0.5 -left-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-500 dark:border-surface-800 animate-pulse"></span>
                </div>
                <div class="flex-1">
                    <p class="font-medium">{{ $activity->user->name ?? 'کاربر حذف شده' }}</p>
                    <p class="text-xs text-slate-500">{{ $activity->page }}</p>
                    <p class="text-xs text-slate-400">{{ $activity->last_active_at?->diffForHumans() ?? 'نامشخص' }}</p>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-500">
                هیچ کاربری آنلاین نیست
            </div>
        @endforelse
    </div>
@endsection