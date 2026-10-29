<div x-data="{ countdown: {{ $cooldown }} }" x-init="
    if (countdown > 0) {
        let interval = setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                clearInterval(interval);
                $wire.set('canResend', true);
                $wire.set('cooldown', 0);
            }
        }, 1000);
    }
">
    {{-- OTP Header --}}
    <div class="mb-8 text-center">
        <div class="flex justify-center mb-4">
            <img src="{{ admin_logo_url() }}" alt="{{ config('app.name') }}" class="object-contain w-16 h-16 shadow-lg animate-pulse-glow rounded-xl shadow-primary-500/20" />
        </div>
        <h2 class="mb-2 text-2xl font-bold text-white ">کد تایید را وارد کنید</h2>
        <p class="px-2 text-sm leading-relaxed text-slate-400">
            کد ۶ رقمی به شماره
            <span dir="ltr" class="inline-block">{{ $maskedContact }}</span>
            ارسال شد
        </p>
    </div>

    {{-- OTP Form --}}
    <form wire:submit="verify" novalidate>
        {{-- OTP Input --}}
        <div class="mb-6">
            <div class="gradient-border-wrap">
                <div class="input-inner">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="otp"
                        maxlength="6"
                        placeholder="------"
                        class="w-full text-center text-3xl font-bold tracking-[0.5em] py-4 px-4 bg-transparent text-white placeholder-slate-600 outline-none"
                        dir="ltr"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        {{ $isLoading ? 'disabled' : '' }}
                        x-ref="otpInput"
                    />
                </div>
            </div>
        </div>

        {{-- Error Message --}}
        @if($errorMessage)
            <div class="p-3 mb-4 text-sm text-center border rounded-xl bg-rose-500/10 border-rose-500/30 text-rose-400">
                {{ $errorMessage }}
            </div>
        @endif

        {{-- Verify Button --}}
        <button
            type="submit"
            class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white flex items-center justify-center gap-2 transition-all duration-150"
            {{ $isLoading ? 'disabled' : '' }}
        >
            @if($isLoading)
                <svg class="w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <span>در حال تایید...</span>
            @else
                <span>تایید و ورود</span>
            @endif
        </button>
    </form>

    {{-- Resend OTP --}}
    <div class="mt-6 text-center">
        <p class="text-sm text-slate-400">
            کد را دریافت نکردید؟
            @if($canResend || $cooldown <= 0)
                <button
                    type="button"
                    wire:click="resendOtp"
                    class="font-medium transition duration-150 cursor-pointer text-primary-400 hover:text-primary-300"
                >
                    ارسال مجدد کد
                </button>
            @else
                <span class="cursor-not-allowed text-slate-500">
                    ارسال مجدد کد
                </span>
            @endif
        </p>
        @if(!$canResend && $cooldown > 0)
            <p class="mt-1 text-xs text-slate-500">
                امکان ارسال مجدد تا
                <span class="font-semibold text-primary-400" x-text="countdown"></span>
                ثانیه دیگر
            </p>
        @endif
    </div>

    {{-- Back to Login --}}
    <div class="mt-4 text-center">
        <a href="{{ route('auth.login') }}" class="text-sm transition duration-150 text-slate-500 hover:text-slate-400">
            بازگشت به صفحه ورود
        </a>
    </div>

    {{-- Development: Show OTP code — فقط وقتی حالت توسعه فعال باشد ($devCode در mount پر می‌شود) --}}
    @if ($devCode)
        <p class="mt-5 text-xs text-center text-amber-400/90" dir="ltr">
            (حالت توسعه — کد ارسالی: {{ $devCode }})
        </p>
    @endif
</div>
