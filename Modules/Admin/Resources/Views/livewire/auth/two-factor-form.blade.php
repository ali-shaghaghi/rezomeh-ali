<div>
    {{-- OTP Form --}}
    <form wire:submit="verify" novalidate>
        <fieldset class="border-0 p-0 m-0 min-w-0">
            <legend class="sr-only">کد تایید شش رقمی</legend>

            {{-- OTP Inputs --}}
            <div
                id="otp-inputs"
                class="grid grid-cols-6 gap-2 sm:gap-3 mb-6"
                dir="ltr"
                role="group"
                aria-label="ارقام کد تایید"
            >
                @for($i = 0; $i < 6; $i++)
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="1"
                        autocomplete="one-time-code"
                        wire:model="otpDigits.{{ $i }}"
                        wire:keydown="handleKeydown($event, {{ $i }})"
                        class="otp-input h-14 rounded-xl border-2 border-slate-700 {{ $otpDigits[$i] ?? '' ? 'filled' : '' }} {{ $errors->has('otp') ? 'error' : '' }}"
                        data-index="{{ $i }}"
                        aria-label="رقم {{ ['اول', 'دوم', 'سوم', 'چهارم', 'پنجم', 'ششم'][$i] }}"
                        {{ $isLoading ? 'disabled' : '' }}
                    />
                @endfor
            </div>

            {{-- Error Message --}}
            @if($errorMessage)
                <p class="text-center text-sm text-rose-400 mb-4" role="alert" aria-live="polite">
                    {{ $errorMessage }}
                </p>
            @endif

            {{-- Verify Button --}}
            <button
                type="submit"
                class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white flex items-center justify-center gap-2"
                {{ $isLoading ? 'disabled' : '' }}
            >
                @if($isLoading)
                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span>در حال پردازش...</span>
                @else
                    <span>تایید و ورود</span>
                @endif
            </button>
        </fieldset>
    </form>

    {{-- Resend Code --}}
    <footer class="mt-6 text-center">
        <p class="text-sm text-slate-400">
            کد را دریافت نکردید؟
            <button
                type="button"
                wire:click="resendOtp"
                class="text-primary-400 font-medium transition duration-200 {{ $canResend ? 'hover:text-primary-300 cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                {{ !$canResend ? 'disabled' : '' }}
            >
                ارسال مجدد کد
            </button>
        </p>
        @if(!$canResend)
            <p class="text-xs text-slate-500 mt-1">
                امکان ارسال مجدد تا
                <span class="text-primary-400 font-semibold tabular-nums" dir="ltr">
                    {{ $formattedCooldown }}
                </span>
            </p>
        @endif
    </footer>
</div>