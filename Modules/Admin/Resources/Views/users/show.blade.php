@extends('admin::layouts.master')

@section('title', 'جزئیات کاربر')

@section('content')
    <!-- Title -->
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">جزئیات کاربر</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $user->name }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
            بازگشت
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Profile Card -->
        <section class="chart-card">
            <div class="text-center">
                <img src="{{ user_avatar_url($user) }}" alt="" class="mx-auto h-24 w-24 rounded-2xl object-cover border-2 border-slate-200 dark:border-slate-700" />
                <h3 class="mt-4 text-xl font-bold">{{ $user->name }}</h3>
                <p class="text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                @if($user->phone)
                    <p class="text-slate-500 dark:text-slate-400">{{ $user->phone }}</p>
                @endif

                <div class="mt-4">
                    @if($user->is_active)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            فعال
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1 text-sm font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            غیرفعال
                        </span>
                    @endif
                </div>

                <div class="mt-4">
                    @foreach($user->roles as $role)
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary-100 px-3 py-1 text-sm font-medium text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                            {{ $role->name }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 space-y-3 border-t border-slate-100 pt-6 dark:border-slate-700">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">تاریخ عضویت</span>
                    <span class="font-medium">{{ $user->created_at->format('Y/m/d') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">آخرین ورود</span>
                    <span class="font-medium">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'هرگز' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">تغییر نقش</span>
                    <form action="{{ route('admin.users.role', $user) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <select name="role" onchange="this.form.submit()" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs dark:border-slate-700 dark:bg-surface-800">
                            <option value="super-admin" {{ $user->hasRole('super-admin') ? 'selected' : '' }}>مدیر ارشد</option>
                            <option value="admin" {{ $user->hasRole('admin') ? 'selected' : '' }}>مدیر</option>
                            <option value="editor" {{ $user->hasRole('editor') ? 'selected' : '' }}>ویرایشگر</option>
                            <option value="support" {{ $user->hasRole('support') ? 'selected' : '' }}>پشتیبانی</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full rounded-xl {{ $user->is_active ? 'border border-rose-300 text-rose-600 hover:bg-rose-50 dark:border-rose-700 dark:text-rose-400' : 'border border-emerald-300 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400' }} px-4 py-2 text-sm font-medium transition">
                        {{ $user->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}
                    </button>
                </form>
            </div>
        </section>

        <!-- Permissions -->
        <section class="chart-card lg:col-span-2">
            <h3 class="mb-4 text-lg font-bold">دسترسی‌ها</h3>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($user->roles->flatMap->permissions as $permission)
                    <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 dark:border-slate-700">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="check" size="4" />
                        </span>
                        <div>
                            <p class="text-sm font-medium">{{ $permission->name }}</p>
                            <p class="text-xs text-slate-500">{{ $permission->group }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection