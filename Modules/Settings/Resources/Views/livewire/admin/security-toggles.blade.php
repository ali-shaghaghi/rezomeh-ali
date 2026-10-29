<div>
    {{-- پیام‌ها --}}
    @if($successMessage)
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-sm text-center" role="status">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm text-center" role="alert">
            {{ $errorMessage }}
        </div>
    @endif

    {{-- ============ ورود دو عاملی (2FA) ============ --}}
    <div class="flex items-start justify-between gap-4 mb-6 pb-6 border-b {{ $theme === 'dark' ? 'border-slate-700/50' : 'border-slate-200 dark:border-slate-700/50' }}">
        <div class="min-w-0">
            <p class="font-bold {{ $theme === 'dark' ? 'text-white' : 'text-slate-800 dark:text-white' }}">ورود دو عاملی (2FA)</p>
            <p class="mt-1 text-xs leading-relaxed {{ $theme === 'dark' ? 'text-slate-400' : 'text-slate-500 dark:text-slate-400' }}">
                پس از ورود موفق، یک کد دومرحله‌ای نیز لازم است.
            </p>
            @if($twoFactorEnabled)
                <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/15 text-emerald-400">فعال</span>
            @else
                <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-500/15 text-slate-400">غیرفعال (پیش‌فرض)</span>
            @endif
        </div>

        <div class="shrink-0 pt-1">
            @if($twoFactorEnabled)
                <button type="button" wire:click="$set('showDisableForm', true)"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg border border-rose-500/40 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition">
                    غیرفعال‌سازی
                </button>
            @else
                <button type="button" wire:click="enableTwoFactor"
                        class="btn-primary px-3 py-1.5 text-xs font-bold rounded-lg" {{ $isLoading ? 'disabled' : '' }}>
                    فعال‌سازی 2FA
                </button>
            @endif
        </div>
    </div>

    {{-- فرم غیرفعال‌سازی 2FA (رمز عبور لازم است) --}}
    @if($showDisableForm && $twoFactorEnabled)
        <div class="mb-6 p-4 rounded-xl border border-rose-500/30 bg-rose-500/5 animate-slide-up">
            <p class="mb-3 text-xs text-rose-400">برای غیرفعال‌سازی ورود دو عاملی، رمز عبور فعلی خود را وارد کنید:</p>
            <form wire:submit="disableTwoFactor" class="flex flex-col sm:flex-row gap-2">
                <input type="password" wire:model="disablePassword" autocomplete="current-password"
                       placeholder="رمز عبور فعلی" dir="ltr"
                       class="flex-1 rounded-xl border-2 border-slate-700 bg-white/5 py-2.5 px-4 text-sm text-white placeholder-slate-500 outline-none focus:border-primary-500"
                       {{ $isLoading ? 'disabled' : '' }} />
                <button type="submit" class="px-4 py-2.5 text-xs font-bold rounded-lg bg-rose-600 text-white hover:bg-rose-500 transition" {{ $isLoading ? 'disabled' : '' }}>
                    {{ $isLoading ? '...' : 'تایید و غیرفعال‌سازی' }}
                </button>
                <button type="button" wire:click="cancelDisable" class="px-4 py-2.5 text-xs font-bold rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700/40 transition">
                    انصراف
                </button>
            </form>
            @error('disablePassword') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
        </div>
    @endif

    {{-- کدهای بازیابی — فقط یک‌بار پس از فعال‌سازی --}}
    @if($showRecoveryCodes && count($recoveryCodes) > 0)
        <div class="mb-6 p-4 rounded-xl border border-amber-500/30 bg-amber-500/5 animate-slide-up">
            <p class="mb-2 text-sm font-bold text-amber-400">⚠️ کدهای بازیابی — فقط همین یک بار نمایش داده می‌شوند:</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3" dir="ltr">
                @foreach($recoveryCodes as $code)
                    <code class="px-2 py-1 text-xs text-center rounded-lg bg-slate-800/60 text-slate-200 border border-slate-700">{{ $code }}</code>
                @endforeach
            </div>
            <button type="button" wire:click="$set('showRecoveryCodes', false)"
                    class="text-xs font-bold text-primary-400 hover:text-primary-300 transition">
                کدها را ذخیره کردم
            </button>
        </div>
    @endif

    {{-- ============ تایید ایمیل ============ --}}
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="font-bold {{ $theme === 'dark' ? 'text-white' : 'text-slate-800 dark:text-white' }}">ارسال ایمیل تایید</p>
            <p class="mt-1 text-xs leading-relaxed {{ $theme === 'dark' ? 'text-slate-400' : 'text-slate-500 dark:text-slate-400' }}">
                با هر ورود، اگر ایمیل شما تایید نشده باشد لینک تایید برایتان ارسال می‌شود.
            </p>
            @if($emailVerificationEnabled)
                <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/15 text-emerald-400">
                    فعال — {{ $emailVerified ? 'ایمیل تایید شده' : 'در انتظار تایید ایمیل' }}
                </span>
                @unless($emailVerified)
                    <button type="button" wire:click="sendVerificationEmail"
                            class="block mt-2 text-xs font-bold text-primary-400 hover:text-primary-300 transition">
                        ارسال مجدد ایمیل تایید
                    </button>
                @endunless
            @else
                <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-500/15 text-slate-400">غیرفعال (پیش‌فرض)</span>
            @endif
        </div>

        <div class="shrink-0 pt-1">
            {{-- سوییچ فعال/غیرفعال --}}
            <button type="button"
                    wire:click="setEmailVerification({{ $emailVerificationEnabled ? 'false' : 'true' }})"
                    class="relative w-11 h-6 rounded-full transition-colors duration-200 {{ $emailVerificationEnabled ? 'bg-primary-500' : 'bg-slate-600' }}"
                    role="switch" aria-checked="{{ $emailVerificationEnabled ? 'true' : 'false' }}">
                <span class="absolute top-0.5 {{ $emailVerificationEnabled ? 'left-0.5' : 'right-0.5' }} w-5 h-5 rounded-full bg-white shadow transition-all duration-200"></span>
            </button>
        </div>
    </div>

    {{-- ============ کد ورود یک‌بارمصرف (OTP) ============ --}}
    <div class="flex items-start justify-between gap-4 mt-6 pt-6 border-t {{ $theme === 'dark' ? 'border-slate-700/50' : 'border-slate-200 dark:border-slate-700/50' }}">
        <div class="min-w-0">
            <p class="font-bold {{ $theme === 'dark' ? 'text-white' : 'text-slate-800 dark:text-white' }}">کد ورود یک‌بارمصرف (OTP)</p>
            <p class="mt-1 text-xs leading-relaxed {{ $theme === 'dark' ? 'text-slate-400' : 'text-slate-500 dark:text-slate-400' }}">
                پس از ورود با رمز عبور، یک کد ۶ رقمی به ایمیل شما ارسال و باید تایید شود.
            </p>
            @if($loginOtpEnabled)
                <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/15 text-emerald-400">فعال</span>
            @else
                <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-500/15 text-slate-400">غیرفعال (پیش‌فرض)</span>
            @endif
        </div>

        <div class="shrink-0 pt-1">
            {{-- سوییچ فعال/غیرفعال --}}
            <button type="button"
                    wire:click="setLoginOtp({{ $loginOtpEnabled ? 'false' : 'true' }})"
                    class="relative w-11 h-6 rounded-full transition-colors duration-200 {{ $loginOtpEnabled ? 'bg-primary-500' : 'bg-slate-600' }}"
                    role="switch" aria-checked="{{ $loginOtpEnabled ? 'true' : 'false' }}">
                <span class="absolute top-0.5 {{ $loginOtpEnabled ? 'left-0.5' : 'right-0.5' }} w-5 h-5 rounded-full bg-white shadow transition-all duration-200"></span>
            </button>
        </div>
    </div>
</div>