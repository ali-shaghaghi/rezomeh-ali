@extends('admin::layouts.master')

@section('title', 'مدیریت کاربران')

@push('styles')
<style>
    .user-card {
        opacity: 0;
        transform: translateY(20px);
    }
    .user-card.show {
        animation: slideUp 0.5s ease-out forwards;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
    {{-- Title --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 animate-slide-up">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت کاربران</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $users->total() }} کاربر</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-6 animate-slide-up chart-card">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجوی نام، ایمیل یا تلفن..." class="flex-1 min-w-[180px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800" />
            <select name="role" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه نقش‌ها</option>
                @foreach(\Modules\Core\Models\Role::all() as $r)
                    <option value="{{ $r->slug }}" {{ request('role') === $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
            </select>
            <button type="submit" class="rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-medium text-white shadow-md shadow-primary-500/30 transition hover:brightness-110">
                اعمال فیلتر
            </button>
        </form>
    </div>
    <div class="mb-6 animate-slide-up chart-card">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.export') }}" class="group flex items-center gap-1.5 rounded-xl bg-primary-500 px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-md shadow-primary-500/30 transition hover:brightness-110">
                <x-icon name="download" size="4" />
                خروجی اکسل
            </a>
            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="group flex items-center gap-1.5 rounded-xl bg-primary-500 px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-md shadow-primary-500/30 transition hover:brightness-110 cursor-pointer">
                    <x-icon name="upload" size="4" />
                    ورودی اکسل
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('admin.users.online') }}" class="flex items-center gap-1.5 rounded-xl bg-primary-500 px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-md shadow-primary-500/30 transition hover:brightness-110">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                آنلاین‌ها
            </a>
        </div>
    </div>


    {{-- Users Grid --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up">
        @forelse($users as $user)
            <section class="flex flex-col role-card chart-card animate-slide-up">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <img src="{{ user_avatar_url($user) }}" alt="" class="object-cover w-10 h-10 rounded-xl" />
                            @if(\Modules\Core\Models\UserActivity::isOnline($user->id))
                                <span class="absolute -bottom-0.5 -left-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-surface-800 animate-pulse-soft"></span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-bold">{{ $user->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                        </div>
                    </div>
                    @if($user->is_active)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            فعال
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            غیرفعال
                        </span>
                    @endif
                </div>

                @if($user->phone)
                    <p class="mb-2 text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $user->phone }}</p>
                @endif

                <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="mt-1 mb-1.5 text-xs font-medium text-slate-500">نقش‌ها:</p>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($user->roles as $role)
                            <span class="rounded-lg bg-primary-100 px-2 py-0.5 text-[10px] font-medium text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                                {{ $role->name }}
                            </span>
                        @empty
                            <span class="text-[10px] text-slate-400">بدون نقش</span>
                        @endforelse
                    </div>
                    <p class="mt-2 text-[10px] text-slate-400">عضویت: {{ $user->created_at->diffForHumans() }}</p>

                </div>


                {{-- Action Buttons --}}
                <div class="flex gap-2 pt-3 mt-2 border-slate-100 dark:border-slate-700">
                    <a href="{{ route('admin.users.show', $user) }}" class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                        مشاهده
                    </a>
                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-lg px-3 py-2 text-xs font-medium transition {{ $user->is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                            {{ $user->is_active ? 'غیرفعال' : 'فعال' }}
                        </button>
                    </form>
                </div>
            </section>
        @empty
            <div class="py-12 text-center sm:col-span-2 text-slate-500">کاربری یافت نشد</div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6 animate-slide-up">
        {{ $users->withQueryString()->links() }}
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.user-card').forEach((card, i) => {
                setTimeout(() => card.classList.add('show'), i * 80);
            });
        });
    </script>
    @endpush
@endsection