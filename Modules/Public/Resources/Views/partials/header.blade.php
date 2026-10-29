@php
    /**
     * رابطه کاربری هدر عمومی (دو‌زبانه):
     *  ┌──────────────────────────────────────────────────────┐
     *  │  ☰ منو          [ لوگو در وسط ]        🌐 زبان  👤 کاربر │
     *  └──────────────────────────────────────────────────────┘
     *
     *  • دکمه ☰ در تمام اندازه‌ها (موبایل و دسکتاپ) نمایش داده می‌شود و
     *    منوی لینک‌ها را به‌صورت کشویی باز می‌کند.
     *  • لوگو در وسط نوار قرار می‌گیرد.
     *  • سمت مقابل: انتخابگر زبان (🇮🇷 fa / 🇬🇧 En) و آواتار کاربر.
     *  • با کلیک روی آواتار زیرمنوی کاربر باز می‌شود:
     *      ۱) پنل کاربری   ۲) سبد خرید   ۳) حالت شب/روز   ۴) زبان
     */
    $siteTitle = \Modules\Settings\Models\Setting::get('site_title', 'علی شقاقی');
    $headerUser = auth()->user();
@endphp

<header id="header" class="fixed top-0 inset-x-0 z-50 transition-all duration-300 px-4 sm:px-6 pt-4 overflow-x-clip">
    <nav class="nav-bar mx-auto max-w-5xl flex items-center gap-2 sm:gap-3" aria-label="{{ __('public.header.open_menu') }}">

        {{-- سمت راست (در RTL): دکمه باز کردن منو — در همه حالت‌ها --}}
        <div class="flex items-center gap-2 shrink-0">
            <button id="mobile-menu-btn" type="button"
                    class="icon-btn group relative"
                    aria-label="{{ __('public.header.open_menu') }}"
                    aria-expanded="false">
                {{-- همبرگر ← ضربدر: دو خط میانی در هم حل می‌شوند (heroicons) --}}
                <x-heroicon name="bars-3" class="h-5 w-5 menu-icon-bars transition-all duration-300" />
                <x-heroicon name="x-mark" class="h-5 w-5 menu-icon-x absolute inset-0 m-auto flex items-center justify-center opacity-0 scale-75 rotate-90 transition-all duration-300" />
            </button>
        </div>

        {{-- لوگو — دقیقاً در وسط نوار --}}
        <a href="{{ route('public.home') }}"
           class="nav-logo absolute left-1/2 -translate-x-1/2 shrink-0 z-10"
           aria-label="{{ $siteTitle }}">
            <img src="{{ admin_logo_url() }}" alt="{{ $siteTitle }}" class="h-9 w-auto max-w-[140px] sm:max-w-[160px] object-contain">
        </a>

        {{-- سمت چپ (در RTL): حساب کاربری (انتخاب زبان داخل زیرمنوی کاربر است) --}}
        <div class="flex items-center gap-2 shrink-0 ms-auto">
            {{-- 👤 حساب کاربری: مهمان → آیکون | واردشده → تصویر پروفایل --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button"
                        class="icon-btn !p-1.5 overflow-hidden flex items-center gap-1.5"
                        :aria-expanded="open"
                        aria-label="{{ __('public.header.account') }}"
                        @click="open = !open">
                    @auth
                        @if (!empty($headerUser->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists('avatars/' . $headerUser->avatar))
                            <img src="{{ user_avatar_url($headerUser) }}" alt="{{ $headerUser->name }}" class="w-7 h-7 rounded-lg object-cover">
                        @else
                            <span class="flex items-center justify-center w-7 h-7 text-primary-600 dark:text-primary-400"><x-icon name="user" size="5" /></span>
                        @endif
                    @else
                        <span class="flex items-center justify-center w-7 h-7 text-slate-500 dark:text-slate-300"><x-icon name="user" size="5" /></span>
                    @endauth
                    <span class="inline-flex transition-transform duration-300" :class="{ 'rotate-180': open }">
                        <x-heroicon name="chevron-down" class="h-3.5 w-3.5 text-slate-400" />
                    </span>
                </button>
                {{-- زیرمنوی کاربر: پنل کاربری ← سبد خرید ← حالت شب/روز ← زبان --}}
                <div x-show="open"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute end-0 top-full mt-2 min-w-[15rem] rounded-xl border border-slate-200/90 bg-white py-1.5 shadow-card dark:border-slate-600 dark:bg-surface-800 z-50">

                    {{-- ۱) پنل کاربری --}}
                    @auth
                        <a href="{{ route('user.dashboard') }}" class="dropdown-item" @click="open = false">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400 shrink-0"><x-icon name="dashboard" size="4" /></span>
                            <span>{{ __('public.header.dashboard') }}</span>
                        </a>
                    @endauth
                    {{-- پنل مدیریت — فقط برای ادمین‌های واردشده از گارد admin (جدول admins) --}}
                    @if (Auth::guard('admin')->check())
                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item" @click="open = false">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-primary-500 to-violet-600 text-white shrink-0"><x-icon name="settings" size="4" /></span>
                            <span class="font-semibold">{{ __('public.header.admin_panel') }}</span>
                        </a>
                    @elseif (! auth()->check())
                        <a href="{{ route('auth.login') }}" class="dropdown-item" @click="open = false">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400 shrink-0"><x-icon name="user" size="4" /></span>
                            <span>{{ __('public.header.login') }}</span>
                        </a>
                        {{-- لینک «ورود مدیران» حذف شد — مسیر ورود ادمین هش‌شده و خصوصی است --}}
                    @endif

                    <div class="h-px my-1 bg-slate-100 dark:bg-slate-700/60"></div>

                    {{-- ۲) سبد خرید (با badge تعداد زنده) --}}
                    <button type="button"
                            class="dropdown-item w-full"
                            x-data="{ count: {{ app(\Modules\Products\Services\CartService::class)->count() }} }"
                            x-on:product-added-to-cart.window="count++"
                            @click="open = false; $dispatch('open-cart-drawer')">
                        <span class="relative flex items-center justify-center w-7 h-7 rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400 shrink-0">
                            <x-icon name="cart" size="4" />
                            <span class="cart-badge !top-0 !left-0 !ring-0" x-show="count > 0" x-text="count"></span>
                        </span>
                        <span>{{ __('public.header.cart') }}</span>
                    </button>

                    {{-- ۳) حالت شب/روز --}}
                    <button type="button" id="theme-toggle" class="dropdown-item w-full">
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400 shrink-0" id="theme-toggle-icon"></span>
                        <span>{{ __('public.header.theme_toggle') }}</span>
                    </button>

                    <div class="h-px my-1 bg-slate-100 dark:bg-slate-700/60"></div>

                    {{-- ۴) زبان انتخابی --}}
                    <div class="px-3 pt-1.5 pb-1">
                        <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('public.header.language') }}</p>
                        <div class="flex items-center gap-1">
                            @foreach (available_locales() as $code => $info)
                                <a href="{{ route('public.locale', $code) }}"
                                   @click="open = false"
                                   title="{{ $info['label'] }}"
                                   class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg text-xs font-medium transition flex-1 justify-center {{ current_locale() === $code ? 'bg-primary-50 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                                    <x-flag :code="$code" class="w-5 h-[13px]" />
                                    <span class="uppercase font-bold">{{ $info['short'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- پنل کشویی منو — در تمام حالت‌ها (موبایل و دسکتاپ) --}}
    <div id="mobile-menu" class="mobile-menu mx-auto max-w-5xl mt-2">
        <ul id="mobile-nav-links" class="mobile-nav-links flex flex-col p-2">
            @if (! request()->routeIs('public.home'))
                <li><a href="{{ route('public.home') }}" class="nav-link block">{{ __('public.nav.home') }}</a></li>
            @endif
            {{-- در صفحه اصلی: اسکرول به بخش‌ها؛ در صفحات دیگر: لینک به صفحه/بخش مربوطه --}}
            @if (request()->routeIs('public.home'))
                <li><a href="#skills" class="nav-link block" data-section="skills">{{ __('public.nav.skills') }}</a></li>
                <li><a href="#experience" class="nav-link block" data-section="experience">{{ __('public.nav.experience') }}</a></li>
                <li><a href="#projects" class="nav-link block" data-section="projects">{{ __('public.nav.projects') }}</a></li>
                <li><a href="#products" class="nav-link block" data-section="products">{{ __('public.nav.products') }}</a></li>
                <li><a href="#consultation" class="nav-link block" data-section="consultation">{{ __('public.nav.consultation') }}</a></li>
                <li><a href="#blog" class="nav-link block" data-section="blog">{{ __('public.nav.blog') }}</a></li>
            @else
                <li><a href="{{ route('public.portfolio.index') }}" class="nav-link block {{ request()->routeIs('public.portfolio.*') ? 'nav-link-active' : '' }}">{{ __('public.nav.portfolio') }}</a></li>
                <li><a href="{{ route('public.products.index') }}" class="nav-link block {{ request()->routeIs('public.products.*') ? 'nav-link-active' : '' }}">{{ __('public.nav.products') }}</a></li>
                <li><a href="{{ route('public.consultation.index') }}" class="nav-link block {{ request()->routeIs('public.consultation.*') ? 'nav-link-active' : '' }}">{{ __('public.nav.consultation') }}</a></li>
                <li><a href="{{ route('public.blog.index') }}" class="nav-link block {{ request()->routeIs('public.blog.*') ? 'nav-link-active' : '' }}">{{ __('public.nav.blog') }}</a></li>
            @endif
        </ul>
    </div>
</header>
