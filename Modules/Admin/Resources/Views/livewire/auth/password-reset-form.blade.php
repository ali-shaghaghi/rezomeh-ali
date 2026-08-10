<div>
    {{-- Step 1: Enter Email --}}
    @if($step === 1)
        <form wire:submit="sendOtp" novalidate>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                    ایمیل
                </label>
                <div class="gradient-border-wrap">
                    <div class="input-inner relative flex items-center">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 z-10">
                            <x-icon name="mail" />
                        </span>
                        <input
                            type="email"
                            id="email"
                            wire:model="email"
                            placeholder="email@example.com"
                            class="w-full bg-transparent py-3 pr-11 pl-4 text-white placeholder-slate-500 outline-none"
                            dir="ltr"
                            {{ $isLoading ? 'disabled' : '' }}
                        />
                    </div>
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            @if($errorMessage)
                <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm text-center" role="alert">
                    {{ $errorMessage }}
                </div>
            @endif

            <button
                type="submit"
                class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white flex items-center justify-center gap-2 transition-all duration-150"
                {{ $isLoading ? 'disabled' : '' }}
            >
                @if($isLoading)
                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span>در حال ارسال...</span>
                @else
                    <span>ارسال کد بازیابی</span>
                @endif
            </button>
        </form>
    @endif

    {{-- Step 2: Enter OTP --}}
    @if($step === 2)
        <form wire:submit="verifyOtp" novalidate>
            @if($successMessage)
                <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm text-center">
                    {{ $successMessage }}
                </div>
            @endif

            <div class="mb-4">
                <label for="otp" class="block text-sm font-medium text-slate-300 mb-2">
                    کد بازیابی
                </label>
                <div class="gradient-border-wrap">
                    <div class="input-inner relative flex items-center">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 z-10">
                            <x-icon name="shield" />
                        </span>
                        <input
                            type="text"
                            id="otp"
                            wire:model="otp"
                            placeholder="کد ۶ رقمی"
                            class="w-full bg-transparent py-3 pr-11 pl-4 text-white placeholder-slate-500 outline-none"
                            dir="ltr"
                            maxlength="6"
                            {{ $isLoading ? 'disabled' : '' }}
                        />
                    </div>
                </div>
                @error('otp')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            @if($errorMessage)
                <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm text-center" role="alert">
                    {{ $errorMessage }}
                </div>
            @endif

            <button
                type="submit"
                class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white flex items-center justify-center gap-2 transition-all duration-150"
                {{ $isLoading ? 'disabled' : '' }}
            >
                @if($isLoading)
                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span>در حال تایید...</span>
                @else
                    <span>تایید کد</span>
                @endif
            </button>
        </form>
    @endif

    {{-- Step 3: New Password --}}
    @if($step === 3)
        <form wire:submit="resetPassword" novalidate>
            @if($successMessage)
                <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm text-center">
                    {{ $successMessage }}
                </div>
            @endif

            @if($errorMessage)
                <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm text-center" role="alert">
                    {{ $errorMessage }}
                </div>
            @endif

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                    رمز عبور جدید
                </label>
                <div class="gradient-border-wrap">
                    <div class="input-inner relative flex items-center">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 z-10">
                            <x-icon name="shield" />
                        </span>
                        <input
                            type="password"
                            id="password"
                            wire:model="newPassword"
                            placeholder="رمز عبور جدید"
                            class="w-full bg-transparent py-3 pr-11 pl-4 text-white placeholder-slate-500 outline-none"
                            {{ $isLoading ? 'disabled' : '' }}
                        />
                    </div>
                </div>
                @error('newPassword')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">
                    تکرار رمز عبور
                </label>
                <div class="gradient-border-wrap">
                    <div class="input-inner relative flex items-center">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 z-10">
                            <x-icon name="shield" />
                        </span>
                        <input
                            type="password"
                            id="password_confirmation"
                            wire:model="newPasswordConfirmation"
                            placeholder="تکرار رمز عبور"
                            class="w-full bg-transparent py-3 pr-11 pl-4 text-white placeholder-slate-500 outline-none"
                            {{ $isLoading ? 'disabled' : '' }}
                        />
                    </div>
                </div>
                @error('newPasswordConfirmation')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            @if($errorMessage)
                <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm text-center" role="alert">
                    {{ $errorMessage }}
                </div>
            @endif

            <button
                type="submit"
                class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white flex items-center justify-center gap-2 transition-all duration-150"
                {{ $isLoading ? 'disabled' : '' }}
            >
                @if($isLoading)
                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span>در حال ذخیره...</span>
                @else
                    <span>ذخیره رمز عبور</span>
                @endif
            </button>
        </form>
    @endif

    {{-- Step 4: Success --}}
    @if($step === 4)
        <div class="text-center">
            @if($successMessage)
                <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm text-center">
                    {{ $successMessage }}
                </div>
            @endif

            <div class="success-check-wrap mb-6">
                <div class="success-check-ring"></div>
                <div class="success-check-circle">
                    <svg class="w-12 h-12" viewBox="0 0 52 52" aria-hidden="true">
                        <circle class="opacity-30" cx="26" cy="26" r="25" fill="none" stroke="#22c55e" stroke-width="2" />
                        <path class="success-check-path" fill="none" d="M14 27l7 7 16-16" />
                    </svg>
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-emerald-400 mb-2">تغییر رمز موفق!</h2>
            <p class="text-sm sm:text-base text-slate-400 mb-8">رمز عبور شما با موفقیت تغییر کرد</p>
            <a href="{{ route('admin.login') }}" class="btn-success w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white text-center block">
                ورود با رمز جدید
            </a>
        </div>
    @endif
</div>