{{--
    تب‌های بخش کاربران: کاربران / ادمین‌ها / نقش‌ها
    $activeTab: 'users' | 'admins' | 'roles'
--}}
@php $activeTab = $activeTab ?? 'users'; @endphp

<div class="flex flex-wrap gap-2 mb-6 animate-slide-up" role="tablist" aria-label="بخش کاربران">
    <a
        href="{{ route('admin.users.index') }}"
        role="tab"
        aria-selected="{{ $activeTab === 'users' ? 'true' : 'false' }}"
        class="rounded-xl px-4 py-2 text-sm font-medium transition {{ $activeTab === 'users'
            ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30'
            : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-surface-800 dark:text-slate-400 dark:hover:bg-slate-700' }}"
    >
        کاربران
    </a>

    <a
        href="{{ route('admin.admins.index') }}"
        role="tab"
        aria-selected="{{ $activeTab === 'admins' ? 'true' : 'false' }}"
        class="rounded-xl px-4 py-2 text-sm font-medium transition {{ $activeTab === 'admins'
            ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30'
            : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-surface-800 dark:text-slate-400 dark:hover:bg-slate-700' }}"
    >
        ادمین‌ها
    </a>

    <a
        href="{{ route('admin.users.roles') }}"
        role="tab"
        aria-selected="{{ $activeTab === 'roles' ? 'true' : 'false' }}"
        class="rounded-xl px-4 py-2 text-sm font-medium transition {{ $activeTab === 'roles'
            ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30'
            : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-surface-800 dark:text-slate-400 dark:hover:bg-slate-700' }}"
    >
        نقش‌ها و دسترسی‌ها
    </a>
</div>
