<div class="glass-card p-6 sm:p-8">
    @if ($successToast)
        <div class="mb-5 flex items-center gap-2 p-4 text-sm font-bold rounded-2xl glass-card !border-emerald-300/60 dark:!border-emerald-700/40 !text-emerald-600 dark:!text-emerald-400"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            <x-icon name="check" size="4" />
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorToast)
        <div class="mb-5 flex items-center gap-2 p-4 text-sm font-bold rounded-2xl glass-card !border-rose-300/60 dark:!border-rose-700/40 !text-rose-600 dark:!text-rose-400"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 8000)" x-show="show" x-transition.opacity.duration.500ms>
            <x-icon name="close" size="4" />
            {{ $errorMessage }}
        </div>
    @endif

    @if ($devMode)
        <div class="mb-5 flex flex-wrap items-center gap-2 p-4 text-xs font-medium rounded-2xl border border-amber-300/60 bg-amber-50 text-amber-700 dark:border-amber-700/40 dark:bg-amber-900/20 dark:text-amber-300">
            <x-icon name="shield" size="4" class="shrink-0" />
            <span>
                <strong>حالت توسعه فعال است:</strong> پیامک‌های خودکار (کد ورود و...) فقط لاگ می‌شوند و به کاوه‌نگار وصل نمی‌شوند.
                برای تست واقعی اتصال، حالت توسعه را در
                <a href="{{ route('admin.settings.group', 'site') }}" class="underline hover:text-amber-900 dark:hover:text-amber-200">تنظیمات → عمومی سایت</a>
                خاموش کنید، سپس از دکمه‌های پایین تست بگیرید.
            </span>
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">
        {{-- فعال‌سازی — سبز وقتی روشن است --}}
        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60 transition-colors has-[:checked]:bg-emerald-50 has-[:checked]:border has-[:checked]:border-emerald-200 dark:has-[:checked]:bg-emerald-900/20 dark:has-[:checked]:border-emerald-800">
            <div>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">فعال‌سازی ارسال پیامک</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">در حالت غیرفعال همه پیامک‌ها فقط با درایور لاگ ثبت می‌شوند</p>
            </div>
            <input type="checkbox" wire:model="isEnabled" class="h-5 w-5 rounded border-slate-300 text-emerald-600 accent-emerald-600 focus:ring-primary-500">
        </label>

        {{-- درایور --}}
        <div>
            <label for="sms-driver" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">ارائه‌دهنده</label>
            <select wire:model="driver" id="sms-driver"
                    class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-800 outline-none transition-all focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                @foreach (\Modules\SMS\Services\SmsService::DRIVERS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('driver') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>

        {{-- کلید API --}}
        <div>
            <label for="sms-api-key" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">کلید API / نام کاربری</label>
            <input type="password" wire:model="apiKey" id="sms-api-key" dir="ltr"
                   placeholder="درایور لاگ به کلید نیاز ندارد"
                   class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-800 outline-none transition-all placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
            @error('apiKey') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>

        {{-- فرستنده --}}
        <div>
            <label for="sms-sender" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">شماره فرستنده</label>
            <input type="text" wire:model="sender" id="sms-sender" dir="ltr"
                   class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-800 outline-none transition-all focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
            @error('sender') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            <p class="mt-1 text-[11px] text-slate-400">فقط فرستنده‌ای که در پنل کاوه‌نگار (بخش «خطوط و ارسال‌کننده‌ها») ثبت کرده‌اید معتبر است؛ شمارهٔ موبایل معمولی پذیرفته نمی‌شود. خالی بگذارید تا ارسال‌کنندهٔ پیش‌فرض حساب استفاده شود.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn-primary !px-6 !py-2.5 !text-xs">ذخیره تنظیمات</button>

            @if ($driver !== 'log')
                <button type="button" wire:click="checkBalance" wire:loading.attr="disabled" wire:target="checkBalance"
                        class="btn-outline !px-4 !py-2.5 !text-xs">
                    بررسی موجودی
                </button>
            @endif

            @if ($balance !== null)
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    موجودی: {{ fa_digits(number_format($balance)) }} تومان
                </span>
            @endif
        </div>

        {{-- تست ارسال واقعی — مستقل از حالت توسعه (اتصال مستقیم به درایور انتخاب‌شده) --}}
        @if ($driver !== 'log')
            <div class="pt-4 border-t border-slate-200/70 dark:border-slate-700/60">
                <label for="sms-test-phone" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">تست ارسال واقعی</label>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="text" wire:model="testPhone" id="sms-test-phone" dir="ltr" placeholder="09123456789"
                           class="w-44 rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100" />
                    <button type="button" wire:click="sendTest" wire:loading.attr="disabled" wire:target="sendTest"
                            class="btn-outline !px-4 !py-2.5 !text-xs">
                        ارسال پیامک تستی
                    </button>
                </div>
                @error('testPhone') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-[11px] text-slate-400">پیامک واقعی با درایور انتخاب‌شده (مثلاً کاوه‌نگار) به این شماره ارسال می‌شود تا اتصال بررسی شود.</p>
            </div>
        @endif
    </form>
</div>