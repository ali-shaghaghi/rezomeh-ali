<header class="sticky top-0 z-30 border-b border-slate-200/80 glass animate-fade-in dark:border-slate-800">
    <div class="flex flex-wrap items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">

        <!-- Mobile Menu Button -->
        <button id="open-sidebar" type="button" class="rounded-xl border border-slate-200 p-2.5 text-slate-600 transition hover:border-primary-300 hover:text-primary-600 lg:hidden dark:border-slate-700" aria-label="باز کردن منو">
            <x-icon name="menu" size="6" />
        </button>

        <!-- Search -->
        <div class="dropdown relative min-w-0 flex-1 sm:max-w-md">
            <span class="pointer-events-none absolute right-3 top-1/2 z-10 -translate-y-1/2 text-slate-400">
                <x-icon name="search" />
            </span>
            <input type="search" placeholder="جستجو..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-11 pl-10 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            <button type="button" data-dropdown-toggle="header-search" aria-expanded="false" class="absolute left-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-200/80 hover:text-primary-600 dark:hover:bg-slate-700" aria-label="فیلتر جستجو">
                <x-icon name="filter" />
            </button>
            <div id="header-search" class="dropdown-panel start-0 w-full">
                <a href="{{ route('admin.users.index') }}" class="dropdown-item">
                    <x-icon name="users" />
                    کاربران
                </a>
                <a href="{{ route('admin.orders.index') }}" class="dropdown-item">
                    <x-icon name="orders" />
                    سفارشات
                </a>
                <a href="{{ route('admin.portfolio.index') }}" class="dropdown-item">
                    <x-icon name="products" />
                    پورتفولیو
                </a>
                <a href="{{ route('admin.analytics.index') }}" class="dropdown-item">
                    <x-icon name="analytics" />
                    گزارش‌ها
                </a>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:mr-auto">
            <!-- Theme Toggle -->
            <button id="theme-toggle" type="button" class="rounded-xl border border-slate-200 p-2.5 text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:hover:text-primary-400" aria-label="تغییر تم">
                <span class="dark:hidden"><x-icon name="moon" /></span>
                <span class="hidden dark:inline"><x-icon name="sun" /></span>
            </button>

            <!-- Notifications -->
            <div class="dropdown relative">
                <button type="button" data-dropdown-toggle="header-notif" aria-expanded="false" class="relative rounded-xl border border-slate-200 p-2.5 text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700" aria-label="اعلان‌ها">
                    <x-icon name="bell" />
                    <span class="absolute left-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white">۳</span>
                </button>
                <div id="header-notif" class="dropdown-panel end-0 w-[min(100vw-2rem,22rem)] sm:w-80">
                    <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-700">
                        <p class="text-sm font-bold">اعلان‌ها</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="dropdown-item">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40">
                            <x-icon name="cart" size="4" />
                        </span>
                        <span>
                            <span class="block font-medium">پرداخت سفارش #۴۸۲۱</span>
                            <span class="text-xs text-slate-500">۲ دقیقه پیش</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.portfolio.index') }}" class="dropdown-item">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/40">
                            <x-icon name="products" size="4" />
                        </span>
                        <span>
                            <span class="block font-medium">موجودی کم: هدفون Sony</span>
                            <span class="text-xs text-slate-500">۴۵ دقیقه پیش</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="dropdown-item">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40">
                            <x-icon name="bell" size="4" />
                        </span>
                        <span>
                            <span class="block font-medium">۲۳ سفارش در انتظار ارسال</span>
                            <span class="text-xs text-slate-500">امروز</span>
                        </span>
                    </a>
                </div>
            </div>

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
                    <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
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