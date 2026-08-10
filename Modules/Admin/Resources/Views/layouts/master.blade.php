<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="@yield('meta-description', 'پنل ادمین')" />
    <title>@yield('title', 'پنل ادمین') | {{ config('app.name', 'AliShaghaghi') }}</title>
    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png" />

    @include('admin::partials.styles')

    @livewireStyles
</head>
<body class="bg-surface-50 text-slate-800 transition-colors duration-500 dark:bg-surface-950 dark:text-slate-200">

    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebar-overlay" class="sidebar-overlay opacity-0 pointer-events-none" aria-hidden="true"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 right-0 z-50 flex h-screen min-h-screen w-72 shrink-0 translate-x-full flex-col border-l border-slate-200/80 bg-white/95 backdrop-blur-xl transition-transform duration-500 ease-out dark:border-slate-800 dark:bg-surface-900 lg:sticky lg:top-0 lg:z-40 lg:max-h-screen lg:translate-x-0">
            <!-- Logo -->
            <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="h-8 w-8 rounded-lg object-contain" />
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

        <!-- Main Content -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Header -->
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
                                <img src="{{ user_avatar_url() }}" alt="" class="h-8 w-8 rounded-lg object-cover" />
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

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <x-admin-alert type="success" message="{{ session('success') }}" />
                @endif
                @if(session('error'))
                    <x-admin-alert type="error" message="{{ session('error') }}" />
                @endif
                @if(session('warning'))
                    <x-admin-alert type="warning" message="{{ session('warning') }}" />
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -left-32 top-0 h-96 w-96 rounded-full bg-primary-400/20 blur-3xl animate-float"></div>
        <div class="absolute -right-32 bottom-0 h-80 w-80 rounded-full bg-violet-400/15 blur-3xl animate-float" style="animation-delay: -3s"></div>
    </div>

    @include('admin::partials.scripts')

    @livewireScripts

    @stack('scripts')
</body>
</html>
