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
            <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.admins.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-users" aria-expanded="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.admins.*') ? 'true' : 'false' }}">
                <x-icon name="users" />
                <span class="flex-1 text-right">کاربران</span>
                <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-600 dark:bg-primary-900/50">۱۲</span>
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
                        <x-icon name="analytics" size="4" />
                        رزومه من
                    </a>
                    <a href="{{ route('admin.resume.education.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.education.*') ? 'active' : '' }}">
                        <x-icon name="shield" size="4" />
                        تحصیلات
                    </a>
                    <a href="{{ route('admin.resume.experience.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.experience.*') ? 'active' : '' }}">
                        <x-icon name="users" size="4" />
                        تجربیات
                    </a>
                    <a href="{{ route('admin.resume.skills.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.skills.*') ? 'active' : '' }}">
                        <x-icon name="star" size="4" />
                        مهارت‌ها
                    </a>
                    <a href="{{ route('admin.resume.portfolio.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.portfolio.index') ? 'active' : '' }}">
                        <x-icon name="products" size="4" />
                        نمونه کار
                    </a>
                    <a href="{{ route('admin.resume.portfolio.categories.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.portfolio.categories.*') ? 'active' : '' }}">
                        <x-icon name="filter" size="4" />
                        دسته‌بندی نمونه کار
                    </a>
                    <a href="{{ route('admin.resume.languages.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.resume.languages.*') ? 'active' : '' }}">
                        <x-icon name="mail" size="4" />
                        زبان‌ها
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

        <!-- Analytics (گزارش‌ها) -->
        <div>
            <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('admin.analytics.*') ? 'open' : '' }}" data-sidebar-dropdown="nav-analytics" aria-expanded="{{ request()->routeIs('admin.analytics.*') ? 'true' : 'false' }}">
                <x-icon name="analytics" />
                <span class="flex-1 text-right">گزارش‌ها</span>
                <x-icon name="chevronDown" />
            </button>
            <div id="nav-analytics" class="sidebar-dropdown-panel {{ request()->routeIs('admin.analytics.*') ? 'open' : '' }}">
                <div class="mr-3 mt-1 space-y-0.5 border-r-2 border-slate-200 pr-3 dark:border-slate-700">
                    <a href="{{ route('admin.analytics.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.analytics.index') ? 'active' : '' }}">
                        <x-icon name="analytics" size="4" />
                        داشبورد گزارش‌ها
                    </a>
                    <a href="{{ route('admin.analytics.sales') }}" class="sidebar-sublink {{ request()->routeIs('admin.analytics.sales') ? 'active' : '' }}">
                        <x-icon name="wallet" size="4" />
                        گزارش فروش
                    </a>
                    <a href="{{ route('admin.analytics.traffic') }}" class="sidebar-sublink {{ request()->routeIs('admin.analytics.traffic') ? 'active' : '' }}">
                        <x-icon name="users" size="4" />
                        ترافیک کاربران
                    </a>
                    <a href="{{ route('admin.analytics.content') }}" class="sidebar-sublink {{ request()->routeIs('admin.analytics.content') ? 'active' : '' }}">
                        <x-icon name="bell" size="4" />
                        گزارش محتوا
                    </a>
                </div>
            </div>
        </div>

        <!-- Products (فروش قالب/CRM) -->
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