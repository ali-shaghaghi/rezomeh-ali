<header class="sticky top-0 z-30 border-b border-slate-200/80 glass animate-fade-in dark:border-slate-800">
    <div class="flex flex-wrap items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">

        <!-- Mobile Menu Button -->
        <button id="open-sidebar" type="button" class="rounded-xl border border-slate-200 p-2.5 text-slate-600 transition hover:border-primary-300 hover:text-primary-600 lg:hidden dark:border-slate-700" aria-label="باز کردن منو">
            <x-icon name="menu" size="6" />
        </button>

        <!-- (جستجوی هدر حذف شد — کاربردی نداشت) -->

        <div class="mr-auto flex items-center gap-2">
            <!-- Theme Toggle -->
            <button id="theme-toggle" type="button" class="rounded-xl border border-slate-200 p-2.5 text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:hover:text-primary-400" aria-label="تغییر تم">
                <span class="dark:hidden"><x-icon name="moon" /></span>
                <span class="hidden dark:inline"><x-icon name="sun" /></span>
            </button>

            <!-- Notifications (زنگ زنده — ماژول Notifications) -->
            <livewire:notifications.bell />

            <!-- User Menu -->
            <div class="dropdown relative">
                <button type="button" data-dropdown-toggle="header-user" aria-expanded="false" class="flex items-center gap-2 rounded-xl border border-slate-200 px-2.5 py-1.5 transition hover:border-primary-300 sm:px-3 sm:py-2 dark:border-slate-700">
                    <img src="{{ asset('img/avatar.svg') }}" alt="" class="h-8 w-8 rounded-lg" width="32" height="32" />
                    <span class="hidden text-sm font-medium sm:inline">{{ Auth::user()->name ?? 'کاربر' }}</span>
                    <x-icon name="chevronDown" />
                </button>
                <div id="header-user" class="dropdown-panel end-0 w-56">
                    <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-700">
                        <p class="text-sm font-bold">{{ Auth::user()->name ?? 'کاربر' }}</p>
                        <p class="text-xs text-slate-500">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                    <a href="{{ route('admin.settings.group', 'profile') }}" class="dropdown-item">
                        <x-icon name="users" />
                        پروفایل
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                        <x-icon name="settings" />
                        تنظیمات
                    </a>
                    <a href="{{ route('admin.analytics.index') }}" class="dropdown-item">
                        <x-icon name="analytics" />
                        گزارش‌ها
                    </a>
                    <div class="border-t border-slate-100 dark:border-slate-700">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item w-full text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20">
                                <x-icon name="logout" />
                                خروج
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>