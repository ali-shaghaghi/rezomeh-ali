<div class="grid gap-6 lg:grid-cols-3">
    {{-- ========== موبایل/تبلت: انتخابگر تب | دسکتاپ (لپ‌تاپ): بدون دکمه، سایدبار همیشگی ========== --}}
    {{-- selectTab: اول منو با انیمیشن بسته می‌شود، بعد از پایان انیمیشن (۳۵۰ms) تب عوض می‌شود --}}
    <div class="lg:col-span-1"
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
        {{-- دکمه تمام-عرض: آیکون + نام بخش فعال + شورون چرخان (به‌جای همبرگر/ضربدر) --}}
        <button type="button"
                class="flex w-full items-center gap-3 rounded-xl border border-slate-200/60 bg-slate-100/80 px-4 py-3 text-slate-700 transition-all duration-300 hover:border-primary-300 hover:text-primary-600 hover:shadow-glow dark:border-slate-700/50 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:border-primary-700 lg:hidden"
                :aria-expanded="tabMenuOpen ? 'true' : 'false'"
                aria-label="باز کردن منوی تنظیمات"
                @click="tabMenuOpen = !tabMenuOpen">
            <span class="flex items-center justify-center text-primary-600 dark:text-primary-400">
                <x-icon name="{{ $activeSchema['icon'] }}" />
            </span>
            <span class="flex-1 text-right text-sm font-bold">{{ $activeSchema['title'] }}</span>
            <span class="inline-flex transition-transform duration-300" :class="{ 'rotate-180': tabMenuOpen }">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path></svg>
            </span>
        </button>

        {{-- ===================== ناوبری تب‌ها (بدون رفرش) ===================== --}}
        {{-- موبایل: پنل کشویی با انیمیشن باز شدن و محو شدن | دسکتاپ: سایدبار همیشه باز --}}
        <aside class="settings-tabs-panel mobile-menu mt-2 lg:mt-0"
               :class="tabMenuOpen ? 'menu-open' : ''">
            <ul class="mobile-nav-links space-y-2 p-1">
                @foreach($schema as $groupKey => $groupDef)
                    <li>
                        <button type="button"
                                @click="selectTab('{{ $groupKey }}')"
                                wire:key="tab-{{ $groupKey }}"
                                class="nav-link w-full {{ $activeTab === $groupKey ? 'active' : '' }}">
                            <x-icon name="{{ $groupDef['icon'] }}" />
                            <span class="flex-1 text-right">{{ $groupDef['short'] }}</span>
                            @if($activeTab === $groupKey)
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse-soft"></span>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>

    {{-- ===================== محتوای تب فعال (با انیمیشن نرم slide-up، بدون ایندیکیتور لودینگ) ===================== --}}
    <div class="space-y-6 lg:col-span-2 animate-slide-up" wire:key="settings-content-{{ $activeTab }}">

        {{-- نوتیفیکیشن موفقیت --}}
        @if($successToast)
            <div class="flex items-center gap-2 px-4 py-3 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
                 x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
                <x-icon name="check" size="4" />
                {{ $successMessage }}
            </div>
        @endif

        {{-- ============ تب: فرم داینامیک (site / smtp / ai) ============ --}}
        {{-- توجه: گروه‌های gateway (مثل payment) فرم داینامیک دارند ولی رندر اختصاصی آکاردئونی دارند —
             بنابراین اولویت با شاخه اختصاصی خودشان است --}}
        @if(!empty($activeSchema['fields']) && empty($activeSchema['gateway']))
            <section class="chart-card" wire:key="form-{{ $activeTab }}">
                <h3 class="mb-1 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="{{ $activeSchema['icon'] }}" />
                    {{ $activeSchema['title'] }}
                </h3>
                <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">تغییرات پس از ذخیره بلافاصله در کل سیستم اعمال می‌شود</p>

                <form wire:submit.prevent="save" class="space-y-5">
                    @foreach($activeSchema['fields'] as $key => $field)
                        <div>
                            @if($field['type'] === 'boolean')
                                {{-- سوییچ بولین — سبز وقتی فعال است --}}
                                <label class="flex items-center justify-between gap-3 p-3 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-700/50 transition-colors has-[:checked]:bg-emerald-50 has-[:checked]:border has-[:checked]:border-emerald-200 dark:has-[:checked]:bg-emerald-900/20 dark:has-[:checked]:border-emerald-800">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</span>
                                    <input type="checkbox" wire:model="values.{{ $key }}"
                                           class="w-10 h-5 rounded-full appearance-none cursor-pointer transition bg-slate-300 checked:bg-emerald-500 relative before:content-[''] before:absolute before:top-0.5 before:right-0.5 before:w-4 before:h-4 before:rounded-full before:bg-white before:transition checked:before:right-5 dark:bg-slate-600" />
                                </label>
                            @elseif($field['type'] === 'select')
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</label>
                                <select wire:model.live="values.{{ $key }}"
                                        class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                    @foreach($field['options'] as $optKey => $optLabel)
                                        <option value="{{ $optKey }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                @error("values.{$key}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @elseif($field['type'] === 'textarea')
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</label>
                                <textarea wire:model.blur="values.{{ $key }}" rows="3"
                                          class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"></textarea>
                                @error("values.{$key}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @else
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</label>
                                <input type="{{ $field['type'] === 'password' ? 'password' : $field['type'] }}"
                                       wire:model.blur="values.{{ $key }}"
                                       dir="{{ in_array($field['type'], ['email', 'number', 'password']) ? 'ltr' : 'auto' }}"
                                       class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                       @if($field['type'] === 'password') placeholder="برای حفظ مقدار فعلی خالی بگذارید" @endif />
                                @error("values.{$key}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @endif
                        </div>
                    @endforeach

                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">ذخیره تنظیمات</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            در حال ذخیره...
                        </span>
                    </button>
                </form>
            </section>

        {{-- ============ تب: پروفایل (اطلاعات کاربر + آواتار) ============ --}}
        @elseif($activeTab === 'profile')
            <section class="chart-card" wire:key="profile-info">
                <h3 class="mb-1 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="users" />
                    اطلاعات پروفایل
                </h3>
                <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">نام و ایمیل حساب کاربری شما</p>
                @livewire('admin.settings.profile-form', key('profile-form'))
            </section>

            <section class="chart-card" wire:key="profile-avatar">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="users" />
                    تصویر پروفایل
                </h3>
                @livewire('admin.settings.avatar-form', key('avatar-form'))
            </section>

        {{-- ============ تب: ظاهر (لوگو + تم) ============ --}}
        @elseif($activeTab === 'appearance')
            <section class="chart-card" wire:key="appearance-logo">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="palette" />
                    لوگوی پنل
                </h3>
                @livewire('admin.settings.logo-form', key('logo-form'))
            </section>

            <section class="chart-card" wire:key="appearance-theme">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="palette" />
                    تم سایت
                </h3>
                <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">تغییر بین حالت روشن و تاریک</span>
                    <button type="button" data-theme-toggle
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium transition rounded-xl bg-primary-500 text-white shadow-md shadow-primary-500/25 hover:brightness-110">
                        <span class="dark:hidden"><x-icon name="moon" size="4" /></span>
                        <span class="hidden dark:inline"><x-icon name="sun" size="4" /></span>
                        تغییر تم
                    </button>
                </div>
            </section>

        {{-- ============ تب: امنیت حساب ============ --}}
        @elseif($activeTab === 'security')
            <section class="chart-card" wire:key="security-password">
                <h3 class="mb-1 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="shield" />
                    تغییر رمز عبور
                </h3>
                <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">امنیت حساب خود را با رمز قوی حفظ کنید</p>
                <div class="p-4 rounded-xl bg-gradient-to-l from-rose-50 to-amber-50/50 dark:from-rose-950/20 dark:to-amber-950/10">
                    @livewire('admin.settings.change-password-form', key('change-password-form'))
                </div>
            </section>

            {{-- امنیت پیشرفته — ورود دو عاملی + تایید ایمیل (اختیاری، پیش‌فرض غیرفعال) --}}
            <section class="chart-card" wire:key="security-toggles">
                <h3 class="mb-1 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="shield" />
                    ورود دو عاملی و تایید ایمیل
                </h3>
                <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">این قابلیت‌ها اختیاری‌اند و به‌صورت پیش‌فرض غیرفعال‌اند — برای هر دو پنل ادمین و کاربری</p>
                <div class="p-4 rounded-xl bg-gradient-to-l from-primary-50 to-indigo-50/50 dark:from-primary-950/20 dark:to-indigo-950/10">
                    @livewire('admin.settings.security-toggles', ['theme' => 'dark'], key('security-toggles'))
                </div>
            </section>

        {{-- ============ تب: درگاه پرداخت (آکاردئونی) ============ --}}
        @elseif($activeTab === 'payment')
            <section class="chart-card" wire:key="payment-gateways">
                <h3 class="mb-1 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="wallet" />
                    درگاه‌های پرداخت
                </h3>
                <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">روی هر درگاه کلیک کنید تا تنظیمات آن باز شود — درگاه باز = درگاه فعال</p>

                <div class="space-y-3" wire:loading.class="opacity-60 pointer-events-none" wire:target="toggleGateway, saveGateway">
                    @foreach([
                        'zarinpal' => ['زرین‌پال', 'پرداخت امن آنلاین با همه کارت‌های عضو شتاب', 'پس‌رنگ زرد — محبوب‌ترین درگاه ایرانی', 'from-amber-400 to-yellow-500', 'text-slate-900', 'ZP'],
                        'idpay' => ['آی‌دی‌پی', 'درگاه پرداخت چندبانکی با پشتیبانی واریز سریع', 'سرویس پرداخت آسان و سریع', 'from-sky-400 to-blue-600', 'text-white', 'ID'],
                        'crypto' => ['کیف پول ارز دیجیتال (USDT)', 'دریافت پرداخت با تتر روی شبکه TRC20/ERC20/BEP20', 'بدون واسطه — مناسب مشتریان خارج از کشور', 'from-violet-400 to-purple-600', 'text-white', '₮'],
                        'manual' => ['کارت به کارت (دستی)', 'انتقال دستی با تأیید ادمین — بدون درگاه آنلاین', 'مناسب سفارش‌های توافقی', 'from-slate-400 to-slate-600', 'text-white', '↓'],
                    ] as $gwKey => [$gwName, $gwDesc, $gwHint, $gwGradient, $gwText, $gwBadge])
                        @php($isOpen = $openGateway === $gwKey)
                        <div class="overflow-hidden border rounded-2xl transition-all duration-300 {{ $isOpen ? 'border-primary-300 shadow-lg shadow-primary-500/10 dark:border-primary-700' : 'border-slate-200 dark:border-slate-700' }}">
                            {{-- هدر آکاردئون --}}
                            <button type="button" wire:click="toggleGateway('{{ $gwKey }}')" class="flex items-center gap-4 w-full p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <span class="flex items-center justify-center w-12 h-12 text-sm font-black rounded-xl bg-gradient-to-bl {{ $gwGradient }} {{ $gwText }} shadow-md">
                                    {{ $gwBadge }}
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="flex items-center gap-2">
                                        <span class="font-bold">{{ $gwName }}</span>
                                        @if(($values['payment_driver'] ?? '') === $gwKey)
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">فعال</span>
                                        @endif
                                        @if($gwKey === 'zarinpal' && ($values['payment_test_mode'] ?? false))
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">سندباکس</span>
                                        @endif
                                    </span>
                                    <span class="block mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $gwDesc }}</span>
                                    <span class="block mt-0.5 text-[10px] text-slate-400 dark:text-slate-500">{{ $gwHint }}</span>
                                </span>
                                <svg class="w-5 h-5 text-slate-400 transition-transform duration-300 {{ $isOpen ? 'rotate-180 text-primary-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- بدنه آکاردئون --}}
                            @if($isOpen)
                                <div class="p-4 space-y-4 border-t border-slate-100 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-800/40 animate-slide-up">
                                    @if($gwKey === 'manual')
                                        <div class="p-3 mb-1 text-xs rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 leading-5">
                                            <p class="font-bold mb-1">کارت به کارت</p>
                                            <p class="text-xs leading-5">تا زمانی که شماره کارت را اینجا ثبت نکنید، گزینه «کارت به کارت» در مرحله پرداخت checkout به مشتری نمایش داده نمی‌شود. پرداخت‌ها از بخش «سفارشات ← ثبت پرداخت» به‌صورت دستی تأیید می‌شوند.</p>
                                        </div>
                                        <div>
                                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">شماره کارت</label>
                                            <input type="text" wire:model.blur="values.payment_card_number" dir="ltr" placeholder="6037-XXXX-XXXX-XXXX"
                                                   class="w-full px-3 py-2 text-sm font-mono tracking-widest transition border rounded-xl border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                            @error('values.payment_card_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                            <p class="mt-1 text-[10px] text-slate-400">خالی بماند = پنهان؛ با ثبت شماره، گزینه کارت به کارت در checkout فعال می‌شود.</p>
                                        </div>
                                    @elseif($gwKey === 'crypto')
                                        {{-- فیلدهای اختصاصی کیف پول ارز دیجیتال --}}
                                        <div class="p-3 mb-1 text-xs rounded-xl bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-300 leading-5">
                                            پس از فعال‌سازی، گزینه «کیف پول ارز دیجیتال» در مرحله پرداخت checkout برای مشتری نمایش داده می‌شود. مبلغ معادل USDT بر اساس نرخ دلار تنظیم‌شده در تب «قیمت ارز» محاسبه می‌گردد.
                                        </div>
                                        <div>
                                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">آدرس کیف پول (Wallet Address) <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model.blur="values.crypto_wallet_address" dir="ltr" placeholder="TVh2xJ9vZ8... (USDT TRC20)"
                                                   class="w-full px-3 py-2 text-sm font-mono transition border rounded-xl border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                            @error('values.crypto_wallet_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                            <p class="mt-1 text-[10px] text-slate-400">آدرس دقیق را کپی کنید — واریز به آدرس اشتباه قابل بازگشت نیست.</p>
                                        </div>
                                        <div>
                                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">شبکه واریز</label>
                                            <select wire:model.live="values.crypto_network"
                                                    class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-white focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                                <option value="trc20">TRC20 (Tron) — کارمزد کم، پیشنهادی</option>
                                                <option value="erc20">ERC20 (Ethereum) — کارمزد بالا</option>
                                                <option value="bep20">BEP20 (BSC)</option>
                                            </select>
                                            @error('values.crypto_network') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- رمزارزهای جایگزین تتر — تتر برای کاربران ایرانی سریعاً شناسایی می‌شود --}}
                                        <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                                <p class="text-xs font-bold text-slate-700 dark:text-slate-200">رمزارزهای جایگزین (BTC / ETH / LTC / TRX)</p>
                                                <span class="text-[10px] text-slate-400">هر رمزارزی که «آدرس + نرخ» داشته باشد در پرداخت checkout نمایش داده می‌شود</span>
                                            </div>
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                @foreach (['btc' => 'بیت‌کوین (BTC)', 'eth' => 'اتریوم (ETH)', 'ltc' => 'لایت‌کوین (LTC)', 'trx' => 'ترون (TRX)'] as $coinKey => $coinLabel)
                                                    <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-600 dark:bg-slate-700/50">
                                                        <p class="mb-2 text-xs font-black text-slate-800 dark:text-slate-100">{{ $coinLabel }}</p>
                                                        <input type="text" wire:model.blur="values.crypto_{{ $coinKey }}_address" dir="ltr" placeholder="آدرس کیف پول"
                                                               class="w-full px-3 py-2 mb-2 text-xs font-mono transition border rounded-lg border-slate-200 bg-white focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                                        <input type="number" wire:model.blur="values.crypto_{{ $coinKey }}_rate" placeholder="نرخ هر واحد به تومان" min="0"
                                                               class="w-full px-3 py-2 text-xs transition border rounded-lg border-slate-200 bg-white focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                                        @error('values.crypto_'.$coinKey.'_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                                        @error('values.crypto_'.$coinKey.'_rate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                                    </div>
                                                @endforeach
                                            </div>
                                            <p class="mt-2 text-[10px] text-slate-400">نرخ هر واحد (تومان) را از صرافی روزانه به‌روزرسانی کنید — مبلغ سفارش به‌صورت خودکار به تعداد رمزارز تبدیل می‌شود. تتر (USDT) با نرخ دلار تب «قیمت ارز» محاسبه می‌شود.</p>
                                        </div>
                                        <label class="flex items-center justify-between gap-3 p-3 rounded-xl cursor-pointer bg-white dark:bg-slate-700/50">
                                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">نمایش گزینه پرداخت ارز دیجیتال در checkout</span>
                                            <input type="checkbox" wire:model="values.crypto_enabled"
                                                   class="w-10 h-5 rounded-full appearance-none cursor-pointer transition bg-slate-300 checked:bg-primary-500 relative before:content-[''] before:absolute before:top-0.5 before:right-0.5 before:w-4 before:h-4 before:rounded-full before:bg-white before:transition checked:before:right-5 dark:bg-slate-600" />
                                        </label>
                                    @else
                                        <div>
                                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">
                                                {{ $gwKey === 'zarinpal' ? 'مرچنت آی‌دی زرین‌پال' : 'کلید API آی‌دی‌پی' }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" wire:model.blur="values.payment_merchant_id" dir="ltr" placeholder="{{ $gwKey === 'zarinpal' ? 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' : 'کلید دریافتی از آی‌دی‌پی' }}"
                                                   class="w-full px-3 py-2 text-sm font-mono transition border rounded-xl border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                            @error('values.payment_merchant_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">آدرس بازگشت (Callback)</label>
                                            <input type="text" wire:model.blur="values.payment_callback_url" dir="ltr" placeholder="https://example.com/payment/callback"
                                                   class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                            @error('values.payment_callback_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        {{-- وضعیت سندباکس — مشخص و سررسیز: فعال = پرداخت آزمایشی / غیرفعال = پرداخت واقعی --}}
                                        <div class="flex items-center justify-between gap-3 rounded-xl border p-3 transition {{ $values['payment_test_mode'] ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950/30' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/30' }}">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100">حالت تست (سندباکس)</p>
                                                <p class="mt-0.5 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                                                    @if ($values['payment_test_mode'])
                                                        فعال — کاربر به صفحه آزمایشی زرین‌پال می‌رود و پول واقعی جابه‌جا نمی‌شود.
                                                    @else
                                                        غیرفعال — پرداخت‌ها از درگاه واقعی زرین‌پال انجام می‌شود.
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-2.5">
                                                <span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $values['payment_test_mode'] ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white' }}">
                                                    {{ $values['payment_test_mode'] ? 'فعال — سندباکس' : 'غیرفعال — واقعی' }}
                                                </span>
                                                <input type="checkbox" wire:model.live="values.payment_test_mode"
                                                       class="w-10 h-5 rounded-full appearance-none cursor-pointer transition bg-slate-300 checked:bg-primary-500 relative before:content-[''] before:absolute before:top-0.5 before:right-0.5 before:w-4 before:h-4 before:rounded-full before:bg-white before:transition checked:before:right-5 dark:bg-slate-600" />
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex flex-wrap gap-2 pt-1">
                                        <button type="button" wire:click="saveGateway('{{ $gwKey }}')" wire:loading.attr="disabled" wire:target="saveGateway"
                                                class="rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                                            <span wire:loading.remove wire:target="saveGateway">ذخیره و فعال‌سازی «{{ $gwName }}»</span>
                                            <span wire:loading wire:target="saveGateway">در حال ذخیره...</span>
                                        </button>
                                        <button type="button" wire:click="toggleGateway('{{ $gwKey }}')"
                                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                                            بستن
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- واحد پول (مشترک بین همه درگاه‌ها) --}}
                    <div class="p-4 border rounded-2xl border-slate-200 dark:border-slate-700">
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">واحد پول نمایشی</label>
                        <input type="text" wire:model.blur="values.payment_currency" placeholder="تومان"
                               class="w-full max-w-xs px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                        @error('values.payment_currency') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        <button type="button" wire:click="saveGateway('{{ $values['payment_driver'] ?? 'zarinpal' }}')" class="mt-3 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            ذخیره واحد پول
                        </button>
                    </div>
                </div>
            </section>

        {{-- ============ تب: شبکه‌های اجتماعی ============ --}}
        @elseif($activeTab === 'social')
            <section class="chart-card" wire:key="social-links">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
                    <h3 class="flex items-center gap-2 text-lg font-bold">
                        <x-icon name="users" />
                        شبکه‌های اجتماعی
                    </h3>
                    <button type="button" wire:click="openSocialCreate"
                            class="group flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35">
                        <span class="flex items-center justify-center w-4 h-4 transition rounded-md bg-white/20 group-hover:rotate-90">
                            <x-icon name="plus" size="3" />
                        </span>
                        افزودن شبکه جدید
                    </button>
                </div>
                <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">لینک‌های شبکه‌های اجتماعی شما — در فرانتاند سایت نمایش داده می‌شوند</p>

                <div class="space-y-3" wire:loading.class="opacity-60 pointer-events-none" wire:target="saveSocial, deleteSocial, moveSocialUp, moveSocialDown">
                    @forelse($socialLinks as $index => $link)
                        <div class="flex flex-wrap items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-700/40">
                            <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                                <x-icon name="users" size="5" />
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="flex items-center gap-2 font-bold">
                                    {{ $link['name'] }}
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-slate-200 text-slate-600 dark:bg-slate-600 dark:text-slate-300">{{ $socialIcons[$link['icon']] ?? $link['icon'] }}</span>
                                </p>
                                <p class="mt-0.5 text-xs truncate text-slate-500 dark:text-slate-400" dir="ltr">{{ $link['url'] }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="moveSocialUp({{ $index }})" {{ $index === 0 ? 'disabled' : '' }}
                                        class="p-1.5 rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 disabled:opacity-30 dark:hover:bg-slate-600" title="انتقال به بالا">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                </button>
                                <button type="button" wire:click="moveSocialDown({{ $index }})" {{ $index === count($socialLinks) - 1 ? 'disabled' : '' }}
                                        class="p-1.5 rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 disabled:opacity-30 dark:hover:bg-slate-600" title="انتقال به پایین">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <button type="button" wire:click="openSocialEdit({{ $index }})"
                                        class="p-1.5 rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-600" title="ویرایش">
                                    <x-icon name="edit" size="4" />
                                </button>
                                <button type="button" wire:click="deleteSocial({{ $index }})" wire:confirm="«{{ $link['name'] }}» حذف شود؟"
                                        class="p-1.5 rounded-lg text-rose-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/30" title="حذف">
                                    <x-icon name="trash" size="4" />
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                            <span class="flex mx-auto mb-3 w-fit text-slate-300 dark:text-slate-600"><x-icon name="users" size="12" /></span>
                            <p class="font-medium text-slate-600 dark:text-slate-300">هنوز شبکه اجتماعی‌ای ثبت نشده است</p>
                            <p class="mt-1 text-xs text-slate-400">اولین شبکه (مثلاً تلگرام یا گیت‌هاب) را اضافه کنید</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>

    {{-- ===================== مودال افزودن/ویرایش شبکه اجتماعی ===================== --}}
    @if($socialModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm animate-slide-up sm:p-4" wire:key="social-modal">
            <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-5 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="users" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $socialEditing ? 'ویرایش شبکه اجتماعی' : 'افزودن شبکه جدید' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $socialEditing ? 'اطلاعات را به‌روزرسانی کنید' : 'اطلاعات شبکه را وارد کنید' }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('socialModalOpen', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                <form wire:submit.prevent="saveSocial" class="space-y-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام شبکه <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="socialForm.name" placeholder="مثال: تلگرام کانال" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                            @error('socialForm.name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">آیکون <span class="text-red-500">*</span></label>
                            <select wire:model.live="socialForm.icon" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                @foreach($socialIcons as $iconKey => $iconLabel)
                                    <option value="{{ $iconKey }}">{{ $iconLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">آدرس <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.blur="socialForm.url" dir="ltr" placeholder="https://t.me/yourchannel یا @username" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                        <p class="mt-1 text-[10px] text-slate-400">می‌توانید @username هم وارد کنید (مخصوص تلگرام/اینستاگرام)</p>
                        @error('socialForm.url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">ترتیب نمایش</label>
                        <input type="number" wire:model.blur="socialForm.order" dir="ltr" min="0" max="999" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                        @error('socialForm.order') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveSocial"
                                class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all disabled:opacity-50">
                            {{ $socialEditing ? 'ذخیره تغییرات' : 'افزودن شبکه' }}
                        </button>
                        <button type="button" wire:click="$set('socialModalOpen', false)"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            لغو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
