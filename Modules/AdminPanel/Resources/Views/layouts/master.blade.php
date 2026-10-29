<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="@yield('meta-description', 'پنل ادمین')" />
    <title>@yield('title', 'پنل ادمین') | {{ config('app.name', 'AliShaghaghi') }}</title>
    <link rel="icon" href="{{ site_favicon_url() }}" />

    @include('adminpanel::partials.styles')

    @livewireStyles
</head>
<body class="transition-colors duration-500 bg-surface-50 text-slate-800 dark:bg-surface-950 dark:text-slate-200">

    @include('partials.splash-screen')

    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebar-overlay" class="opacity-0 pointer-events-none sidebar-overlay" aria-hidden="true"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 right-0 z-50 flex flex-col h-screen min-h-screen transition-transform duration-500 ease-out translate-x-full border-l w-72 shrink-0 border-slate-200/80 bg-white/95 backdrop-blur-xl dark:border-slate-800 dark:bg-surface-900 lg:sticky lg:top-0 lg:z-40 lg:max-h-screen lg:translate-x-0">
            <!-- Logo — کلیک روی لوگو به صفحه اصلی سایت می‌رود -->
            <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                <a href="{{ route('public.home') }}" class="flex items-center gap-3" title="مشاهده سایت">
                    <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="object-contain w-8 h-8 rounded-lg" />
                    <div>
                        <h1 class="text-lg font-bold gradient-text">پنل ادمین</h1>
                        <p class="text-xs text-slate-500">{{ config('app.name', 'AliShaghaghi') }}</p>
                    </div>
                </a>
                <button id="close-sidebar" type="button" class="p-2 transition rounded-xl text-slate-500 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800" aria-label="بستن منو">
                    <x-icon name="close" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto" aria-label="منوی اصلی">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <x-icon name="dashboard" />
                    داشبورد
                </a>

                <!-- Users -->
                <div>
                    <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.admins.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-users" aria-expanded="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.admins.*') ? 'true' : 'false' }}">
                        <x-icon name="users" />
                        <span class="flex-1 text-right">کاربران</span>
                        <x-icon name="chevronDown" />
                    </button>
                    <div id="nav-users" class="sidebar-dropdown-panel {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.admins.*') ? 'open' : '' }}">
                        <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                            <a href="{{ route('admin.users.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                                <x-icon name="users" size="4" />
                                لیست کاربران
                            </a>
                            <a href="{{ route('admin.admins.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                                <x-icon name="shield" size="4" />
                                ادمین‌ها
                            </a>
                            <a href="{{ route('admin.users.roles') }}" class="sidebar-sublink {{ request()->routeIs('admin.users.roles') ? 'active' : '' }}">
                                <x-icon name="shield" size="4" />
                                نقش‌ها
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Resume (رزومه) -->
                <div>
                    <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.resume.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-resume" aria-expanded="{{ request()->routeIs('admin.resume.*') ? 'true' : 'false' }}">
                        <x-icon name="products" />
                        <span class="flex-1 text-right">رزومه</span>
                        <x-icon name="chevronDown" />
                    </button>
                    <div id="nav-resume" class="sidebar-dropdown-panel {{ request()->routeIs('admin.resume.*') ? 'open' : '' }}">
                        <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                            <a href="{{ route('admin.resume.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.index') ? 'active' : '' }}">
                                <x-icon name="analytics" size="5" />
                                رزومه من
                            </a>
                            <a href="{{ route('admin.resume.education.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.education.*') ? 'active' : '' }}">
                                <x-icon name="shield" size="5" />
                                تحصیلات
                            </a>
                            <a href="{{ route('admin.resume.experience.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.experience.*') ? 'active' : '' }}">
                                <x-icon name="users" size="5" />
                                تجربیات
                            </a>
                            <a href="{{ route('admin.resume.skills.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.skills.*') ? 'active' : '' }}">
                                <x-icon name="star" size="5" />
                                مهارت‌ها
                            </a>
                            <a href="{{ route('admin.resume.languages.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.languages.*') ? 'active' : '' }}">
                                <x-icon name="mail" size="5" />
                                زبان‌ها
                            </a>
                            {{-- نمونه کار + زیرمنوی دسته‌بندی‌ها --}}
                            <a href="{{ route('admin.resume.portfolio.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.portfolio.index') or request()->routeIs('admin.resume.portfolio.show') or request()->routeIs('admin.resume.portfolio.create') or request()->routeIs('admin.resume.portfolio.edit') ? 'active' : '' }}">
                                <x-icon name="products" size="5" />
                                نمونه کار
                            </a>
                            <a href="{{ route('admin.resume.portfolio.categories.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.portfolio.categories.*') ? 'active' : '' }}">
                                <x-icon name="filter" size="5" />
                                دسته‌بندی نمونه کار
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
                            <a href="{{ route('admin.blog.categories.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">
                                <x-icon name="filter" size="4" />
                                دسته‌بندی‌ها
                            </a>
                            <a href="{{ route('admin.blog.tags.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.blog.tags.*') ? 'active' : '' }}">
                                <x-icon name="star" size="4" />
                                برچسب‌ها
                            </a>
                            <a href="{{ route('admin.blog.comments.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.blog.comments.*') ? 'active' : '' }}">
                                <x-icon name="mail" size="4" />
                                نظرات
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Products (محصولات) -->
                <div>
                    <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.products.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-products" aria-expanded="{{ request()->routeIs('admin.products.*') ? 'true' : 'false' }}">
                        <x-icon name="products" />
                        <span class="flex-1 text-right">محصولات</span>
                        <x-icon name="chevronDown" />
                    </button>
                    <div id="nav-products" class="sidebar-dropdown-panel {{ request()->routeIs('admin.products.*') ? 'open' : '' }}">
                        <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                            <a href="{{ route('admin.products.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                                <x-icon name="products" size="4" />
                                لیست محصولات
                            </a>
                            <a href="{{ route('admin.products.categories') }}" class="sidebar-sublink {{ request()->routeIs('admin.products.categories') ? 'active' : '' }}">
                                <x-icon name="filter" size="4" />
                                دسته‌بندی‌ها
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

                <!-- Projects (پروژه‌ها — کاتالوگ قیمت‌گذاری + درخواست‌ها) -->
                <div>
                    <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.projectdev.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-projects" aria-expanded="{{ request()->routeIs('admin.projectdev.*') ? 'true' : 'false' }}">
                        <x-icon name="products" />
                        <span class="flex-1 text-right">پروژه‌ها</span>
                        <x-icon name="chevronDown" />
                    </button>
                    <div id="nav-projects" class="sidebar-dropdown-panel {{ request()->routeIs('admin.projectdev.*') ? 'open' : '' }}">
                        <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                            <a href="{{ route('admin.projectdev.requests') }}" class="sidebar-sublink {{ request()->routeIs('admin.projectdev.requests') ? 'active' : '' }}">
                                <x-icon name="bell" size="4" />
                                درخواست‌های پروژه
                            </a>
                            <a href="{{ route('admin.projectdev.types') }}" class="sidebar-sublink {{ request()->routeIs('admin.projectdev.types') ? 'active' : '' }}">
                                <x-icon name="wallet" size="4" />
                                قیمت‌گذاری (انواع و ویژگی‌ها)
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

                <!-- SMS (پیامک) -->
                <div>
                    <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.sms.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-sms" aria-expanded="{{ request()->routeIs('admin.sms.*') ? 'true' : 'false' }}">
                        <x-icon name="mail" />
                        <span class="flex-1 text-right">پیامک</span>
                        <x-icon name="chevronDown" />
                    </button>
                    <div id="nav-sms" class="sidebar-dropdown-panel {{ request()->routeIs('admin.sms.*') ? 'open' : '' }}">
                        <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                            <a href="{{ route('admin.sms.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.sms.index') ? 'active' : '' }}">
                                <x-icon name="settings" size="4" />
                                تنظیمات
                            </a>
                            <a href="{{ route('admin.sms.logs') }}" class="sidebar-sublink {{ request()->routeIs('admin.sms.logs') ? 'active' : '' }}">
                                <x-icon name="analytics" size="4" />
                                لاگ‌ها
                            </a>
                            <a href="{{ route('admin.sms.templates') }}" class="sidebar-sublink {{ request()->routeIs('admin.sms.templates') ? 'active' : '' }}">
                                <x-icon name="products" size="4" />
                                قالب‌ها
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Consultation (مشاوره) -->
                <div>
                    <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.consultations.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-consultations" aria-expanded="{{ request()->routeIs('admin.consultations.*') ? 'true' : 'false' }}">
                        <x-icon name="users" />
                        <span class="flex-1 text-right">مشاوره</span>
                        <x-icon name="chevronDown" />
                    </button>
                    <div id="nav-consultations" class="sidebar-dropdown-panel {{ request()->routeIs('admin.consultations.*') ? 'open' : '' }}">
                        <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                            <a href="{{ route('admin.consultations.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.consultations.index') ? 'active' : '' }}">
                                <x-icon name="bell" size="4" />
                                رزروها
                            </a>
                            <a href="{{ route('admin.consultations.consultants') }}" class="sidebar-sublink {{ request()->routeIs('admin.consultations.consultants') ? 'active' : '' }}">
                                <x-icon name="users" size="4" />
                                مشاوران
                            </a>
                            <a href="{{ route('admin.consultations.schedules') }}" class="sidebar-sublink {{ request()->routeIs('admin.consultations.schedules') ? 'active' : '' }}">
                                <x-icon name="dashboard" size="4" />
                                زمان‌بندی
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Analytics Reports -->
                <div>
                    <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.analytics.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-analytics" aria-expanded="{{ request()->routeIs('admin.analytics.*') ? 'true' : 'false' }}">
                        <x-icon name="analytics" />
                        <span class="flex-1 text-right">گزارش‌ها</span>
                        <x-icon name="chevronDown" />
                    </button>
                    <div id="nav-analytics" class="sidebar-dropdown-panel {{ request()->routeIs('admin.analytics.*') ? 'open' : '' }}">
                        <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                            <a href="{{ route('admin.analytics.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.analytics.index') ? 'active' : '' }}">
                                <x-icon name="dashboard" size="4" />
                                نمای کلی
                            </a>
                            <a href="{{ route('admin.analytics.sales') }}" class="sidebar-sublink {{ request()->routeIs('admin.analytics.sales') ? 'active' : '' }}">
                                <x-icon name="cart" size="4" />
                                فروش
                            </a>
                            <a href="{{ route('admin.analytics.traffic') }}" class="sidebar-sublink {{ request()->routeIs('admin.analytics.traffic') ? 'active' : '' }}">
                                <x-icon name="trendUp" size="4" />
                                ترافیک
                            </a>
                            <a href="{{ route('admin.analytics.content') }}" class="sidebar-sublink {{ request()->routeIs('admin.analytics.content') ? 'active' : '' }}">
                                <x-icon name="edit" size="4" />
                                محتوا
                            </a>
                        </div>
                    </div>
                </div>


                <!-- چت آنلاین (#۲۱) -->
                <a href="{{ route('admin.chat.index') }}" class="nav-link {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                    <x-icon name="bell" />
                    <span class="flex-1 text-right">چت آنلاین</span>
                    @php $openChats = \Modules\Contact\Models\ChatConversation::where('status', 'open')->count(); @endphp
                    @if ($openChats > 0)
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">{{ fa_digits($openChats) }}</span>
                    @endif
                </a>

                <!-- SEO -->
                <a href="{{ route('admin.seo.index') }}" class="nav-link {{ request()->routeIs('admin.seo.*') ? 'active' : '' }}">
                    <x-icon name="trendUp" />
                    <span class="flex-1 text-right">مدیریت سئو</span>
                </a>

                <!-- AI Agent -->
                <a href="{{ route('admin.ai.index') }}" class="nav-link {{ request()->routeIs('admin.ai.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.028-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.028.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                    <span class="flex-1 text-right">دستیار هوشمند</span>
                </a>

                <!-- گالری تصاویر -->
                <a href="{{ route('admin.gallery.index') }}" class="nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}">
                    <x-icon name="palette" />
                    <span class="flex-1 text-right">گالری تصاویر</span>
                </a>

                <!-- Settings -->
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <x-icon name="settings" />
                    <span class="flex-1 text-right">تنظیمات</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-w-0">
            <!-- Header -->
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

                        <!-- Notifications — داینامیک از ماژول Notifications (polling ۳۰s) -->
                        @livewire('notifications.bell')

                        <!-- User Menu -->
                        <div class="relative dropdown">
                            <button type="button" data-dropdown-toggle="header-user" aria-expanded="false" class="flex items-center gap-2 rounded-xl border border-slate-200 px-2.5 py-1.5 transition hover:border-primary-300 sm:px-3 sm:py-2 dark:border-slate-700">
                                <img src="{{ user_avatar_url() }}" alt="" class="object-cover w-8 h-8 rounded-lg" />
                                <span class="hidden text-sm font-medium sm:inline">{{ Auth::user()->name ?? 'کاربر' }}</span>
                                <x-icon name="chevronDown" />
                            </button>
                            <div id="header-user" class="w-56 dropdown-panel end-0">
                                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
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
                                        <button type="submit" class="w-full dropdown-item text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20">
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
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10" aria-hidden="true">
        <div class="absolute top-0 rounded-full -left-32 h-96 w-96 bg-primary-400/20 blur-3xl animate-float"></div>
        <div class="absolute bottom-0 rounded-full -right-32 h-80 w-80 bg-violet-400/15 blur-3xl animate-float" style="animation-delay: -3s"></div>
    </div>

    @include('adminpanel::partials.scripts')

    {{-- ویجت شناور تماس — روی همه صفحات پنل ادمین --}}
    @livewire('contact.floating-widget')

    @livewireScripts

    {{--
        همگام‌ساز وضعیت سایدبار موبایل:
        اگر سایدبار بسته باشد ولی overlay هنوز فعال بماند، کل صفحه تار و اسکرول قفل می‌شود
        (منوی موبایل «درست نمایش نمی‌داد»). این بلوك وضعیت را بعد از هر ناوبری و قبل از رفتن
        به صفحه بعد ریست می‌کند + آیتم فعال منو را هنگام باز شدن در دید قرار می‌دهد.
    --}}
    <script>
        (function () {
            function closeSidebar() {
                var sb = document.getElementById('sidebar');
                var ov = document.getElementById('sidebar-overlay');
                if (sb) sb.classList.add('translate-x-full');
                if (ov) ov.classList.add('opacity-0', 'pointer-events-none');
                document.body.classList.remove('overflow-hidden');
            }

            function sync() {
                var sb = document.getElementById('sidebar');
                if (sb && sb.classList.contains('translate-x-full')) closeSidebar();
            }

            document.addEventListener('DOMContentLoaded', sync);
            document.addEventListener('livewire:navigated', sync);

            // کلیک روی لینک‌های منو → سایدبار موبایل قبل از رفتن به صفحه بعد بسته می‌شود
            // + اسکرول به آیتم فعال (در منوهای بلند، آیتم فعال زیر خط دید بود)
            document.addEventListener('click', function (e) {
                if (!e.target.closest) return;
                if (e.target.closest('#sidebar a')) closeSidebar();
                if (e.target.closest('#open-sidebar')) {
                    var active = document.querySelector('#sidebar .nav-link.active, #sidebar .sidebar-sublink.active');
                    if (active) setTimeout(function () { active.scrollIntoView({ block: 'nearest' }); }, 350);
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
