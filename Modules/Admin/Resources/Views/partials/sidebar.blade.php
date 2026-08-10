<!-- Overlay -->
<div id="sidebar-overlay" class="sidebar-overlay opacity-0 pointer-events-none" aria-hidden="true"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 right-0 z-50 flex h-screen min-h-screen w-72 shrink-0 translate-x-full flex-col border-l border-slate-200/80 bg-white/95 backdrop-blur-xl transition-transform duration-500 ease-out dark:border-slate-800 dark:bg-surface-900 lg:sticky lg:top-0 lg:z-40 lg:max-h-screen lg:translate-x-0">

    <!-- Logo -->
    <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <span class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-bl from-primary-500 to-violet-600 text-white shadow-lg shadow-primary-500/40 animate-float">
                <x-icon name="dashboard" />
                <span class="absolute -top-0.5 -left-0.5 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-white dark:ring-surface-900 animate-pulse-soft"></span>
            </span>
            <div>
                <h1 class="text-lg font-bold gradient-text">پنل ادمین</h1>
                <p class="text-xs text-slate-500">{{ config('app.name', 'AliShaghaghi') }}</p>
            </div>
        </a>
        <button id="close-sidebar" type="button" class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800" aria-label="بستن منو">
            <x-icon name="close" />
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-1 overflow-y-auto p-4" aria-label="منوی اصلی">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <x-icon name="dashboard" />
            داشبورد
        </a>

        <!-- Users -->
        <div>
            <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.users.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-users" aria-expanded="{{ request()->routeIs('admin.users.*') ? 'true' : 'false' }}">
                <x-icon name="users" />
                <span class="flex-1 text-right">کاربران</span>
                <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-600 dark:bg-primary-900/50">۱۲</span>
                <x-icon name="chevronDown" />
            </button>
            <div id="nav-users" class="sidebar-dropdown-panel {{ request()->routeIs('admin.users.*') ? 'open' : '' }}">
                <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <x-icon name="users" size="4" />
                        لیست کاربران
                    </a>
                    <a href="{{ route('admin.users.roles') }}" class="sidebar-sublink {{ request()->routeIs('admin.users.roles') ? 'active' : '' }}">
                        <x-icon name="shield" size="4" />
                        نقش‌ها
                    </a>
                </div>
            </div>
        </div>

        <!-- Portfolio -->
        <div>
            <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.portfolio.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-portfolio" aria-expanded="{{ request()->routeIs('admin.portfolio.*') ? 'true' : 'false' }}">
                <x-icon name="products" />
                <span class="flex-1 text-right">پورتفولیو</span>
                <x-icon name="chevronDown" />
            </button>
            <div id="nav-portfolio" class="sidebar-dropdown-panel {{ request()->routeIs('admin.portfolio.*') ? 'open' : '' }}">
                <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                    <a href="{{ route('admin.portfolio.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.portfolio.index') ? 'active' : '' }}">
                        <x-icon name="products" size="4" />
                        پروژه‌ها
                    </a>
                    <a href="{{ route('admin.portfolio.categories') }}" class="sidebar-sublink {{ request()->routeIs('admin.portfolio.categories') ? 'active' : '' }}">
                        <x-icon name="filter" size="4" />
                        دسته‌بندی‌ها
                    </a>
                </div>
            </div>
        </div>

        <!-- Blog -->
        <div>
            <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.blog.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-blog" aria-expanded="{{ request()->routeIs('admin.blog.*') ? 'true' : 'false' }}">
                <x-icon name="analytics" />
                <span class="flex-1 text-right">وبلاگ</span>
                <x-icon name="chevronDown" />
            </button>
            <div id="nav-blog" class="sidebar-dropdown-panel {{ request()->routeIs('admin.blog.*') ? 'open' : '' }}">
                <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                    <a href="{{ route('admin.blog.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.blog.index') ? 'active' : '' }}">
                        <x-icon name="analytics" size="4" />
                        مقالات
                    </a>
                    <a href="{{ route('admin.blog.categories') }}" class="sidebar-sublink {{ request()->routeIs('admin.blog.categories') ? 'active' : '' }}">
                        <x-icon name="filter" size="4" />
                        دسته‌بندی‌ها
                    </a>
                    <a href="{{ route('admin.blog.tags') }}" class="sidebar-sublink {{ request()->routeIs('admin.blog.tags') ? 'active' : '' }}">
                        <x-icon name="star" size="4" />
                        برچسب‌ها
                    </a>
                </div>
            </div>
        </div>

        <!-- Orders -->
        <div>
            <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.orders.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-orders" aria-expanded="{{ request()->routeIs('admin.orders.*') ? 'true' : 'false' }}">
                <x-icon name="orders" />
                <span class="flex-1 text-right">سفارشات</span>
                <x-icon name="chevronDown" />
            </button>
            <div id="nav-orders" class="sidebar-dropdown-panel {{ request()->routeIs('admin.orders.*') ? 'open' : '' }}">
                <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                    <a href="{{ route('admin.orders.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                        <x-icon name="orders" size="4" />
                        لیست سفارشات
                    </a>
                    <a href="{{ route('admin.orders.pending') }}" class="sidebar-sublink {{ request()->routeIs('admin.orders.pending') ? 'active' : '' }}">
                        <x-icon name="bell" size="4" />
                        در انتظار
                    </a>
                </div>
            </div>
        </div>

        <!-- Tickets -->
        <div>
            <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.tickets.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-tickets" aria-expanded="{{ request()->routeIs('admin.tickets.*') ? 'true' : 'false' }}">
                <x-icon name="mail" />
                <span class="flex-1 text-right">تیکت‌ها</span>
                <x-icon name="chevronDown" />
            </button>
            <div id="nav-tickets" class="sidebar-dropdown-panel {{ request()->routeIs('admin.tickets.*') ? 'open' : '' }}">
                <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                    <a href="{{ route('admin.tickets.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.tickets.index') ? 'active' : '' }}">
                        <x-icon name="mail" size="4" />
                        لیست تیکت‌ها
                    </a>
                    <a href="{{ route('admin.tickets.open') }}" class="sidebar-sublink {{ request()->routeIs('admin.tickets.open') ? 'active' : '' }}">
                        <x-icon name="bell" size="4" />
                        باز
                    </a>
                </div>
            </div>
        </div>

        <!-- Analytics -->
        <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            <x-icon name="analytics" />
            گزارش‌ها
        </a>

        <!-- Settings -->
        <div>
            <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.settings.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-settings" aria-expanded="{{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }}">
                <x-icon name="settings" />
                <span class="flex-1 text-right">تنظیمات</span>
                <x-icon name="chevronDown" />
            </button>
            <div id="nav-settings" class="sidebar-dropdown-panel {{ request()->routeIs('admin.settings.*') ? 'open' : '' }}">
                <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                    <a href="{{ route('admin.settings.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                        <x-icon name="settings" size="4" />
                        عمومی
                    </a>
                    <a href="{{ route('admin.settings.security') }}" class="sidebar-sublink {{ request()->routeIs('admin.settings.security') ? 'active' : '' }}">
                        <x-icon name="shield" size="4" />
                        امنیت
                    </a>
                </div>
            </div>
        </div>
    </nav>
</aside>