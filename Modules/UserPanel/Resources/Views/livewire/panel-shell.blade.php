<div class="grid grid-cols-1 gap-6 lg:grid-cols-3"
     x-data="{
        tabMenuOpen: false,
        selectTab(key) {
            if (! this.tabMenuOpen) { $wire.switchTab(key); return; }
            this.tabMenuOpen = false;
            setTimeout(() => $wire.switchTab(key), 350);
        }
     }"
     @click.outside="tabMenuOpen = false"
     @keydown.escape.window="tabMenuOpen = false">

    {{-- ========== انتخابگر بخش‌ها (الگوی سایدبار تنظیمات ادمین) ========== --}}
    <div class="lg:col-span-1">
        {{-- دکمه تمام‌عرض موبایل: آیکون + نام بخش فعال + شورون چرخان --}}
        <button type="button"
                class="flex w-full items-center gap-3 rounded-xl border border-slate-200/60 bg-slate-100/80 px-4 py-3 text-slate-700 transition-all duration-300 hover:border-primary-300 hover:text-primary-600 hover:shadow-glow dark:border-slate-700/50 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:border-primary-700 lg:hidden"
                :aria-expanded="tabMenuOpen ? 'true' : 'false'"
                aria-label="{{ __('panel.open_menu') }}"
                @click="tabMenuOpen = !tabMenuOpen">
            <span class="flex items-center justify-center text-primary-600 dark:text-primary-400">
                <x-icon name="dashboard" />
            </span>
            <span class="flex-1 text-right text-sm font-bold">{{ $tabs[$tab]['label'] }}</span>
            <span class="inline-flex transition-transform duration-300" :class="{ 'rotate-180': tabMenuOpen }">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path></svg>
            </span>
        </button>

        {{-- ناوبری بخش‌ها (موبایل: پنل کشویی | دسکتاپ ≥1024px: همیشه باز) --}}
        <aside class="settings-tabs-panel mobile-menu mt-2 lg:mt-0"
               :class="tabMenuOpen ? 'menu-open' : ''">
            <ul class="mobile-nav-links space-y-2 p-1">
                @foreach ($tabs as $key => $def)
                    <li>
                        <button type="button"
                                @click="selectTab('{{ $key }}')"
                                wire:key="tab-{{ $key }}"
                                class="nav-link w-full {{ $tab === $key ? 'active' : '' }}">
                            <x-icon name="{{ $def['icon'] }}" />
                            <span class="flex-1 text-right">{{ $def['label'] }}</span>
                            @if (($badges[$key] ?? 0) > 0)
                                <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-bold text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">{{ fa_digits($badges[$key]) }}</span>
                            @endif
                            @if ($tab === $key)
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse-soft"></span>
                            @endif
                        </button>
                    </li>
                @endforeach

                {{-- خروج از حساب کاربری --}}
                <li>
                    <form method="POST" action="{{ route('auth.logout') }}" class="pt-3 mt-3 border-t border-slate-200/70 dark:border-slate-700/50"
                          onsubmit="return confirm('{{ __('panel.logout_confirm') }}');">
                        @csrf
                        <button type="submit" class="nav-link w-full !text-rose-600 dark:!text-rose-400 hover:!bg-rose-50 dark:hover:!bg-rose-950/40">
                            <x-icon name="logout" />
                            <span class="flex-1 text-right">{{ __('panel.logout') }}</span>
                        </button>
                    </form>
                </li>
            </ul>
        </aside>
    </div>

    {{-- ========== محتوای بخش فعال (بدون رفرش — هر بخش از ماژول خودش) ========== --}}
    <div class="space-y-6 lg:col-span-2 animate-slide-up" wire:key="panel-content-{{ $tab }}">
        @if (session('success'))
            <div class="flex items-center gap-2 p-4 text-sm font-bold rounded-2xl glass-card !border-emerald-300/60 dark:!border-emerald-700/40 !text-emerald-600 dark:!text-emerald-400">
                <x-icon name="check" size="4" />
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-2 p-4 text-sm font-bold rounded-2xl glass-card !border-rose-300/60 dark:!border-rose-700/40 !text-rose-600 dark:!text-rose-400">
                <x-icon name="close" size="4" />
                {{ session('error') }}
            </div>
        @endif

        <livewire:is component="{{ $tabs[$tab]['component'] }}" :key="'panel-'.$tab" />
    </div>
</div>
