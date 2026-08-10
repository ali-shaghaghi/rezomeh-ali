<div>
    <h3 class="text-lg font-bold text-white mb-4">تغییر رمز عبور</h3>

    @if($successMessage)
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm text-center">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm text-center">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit="changePassword" novalidate class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">رمز عبور فعلی</label>
            <input
                type="password"
                wire:model="currentPassword"
                class="w-full rounded-xl border-2 border-slate-700 bg-white/5 py-3 px-4 text-white placeholder-slate-500 outline-none transition-all duration-200 focus:border-primary-500 focus:bg-primary-500/5"
                placeholder="رمز عبور فعلی را وارد کنید"
                {{ $isLoading ? 'disabled' : '' }}
            />
            @error('currentPassword')
                <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">رمز عبور جدید</label>
            <input
                type="password"
                wire:model="newPassword"
                class="w-full rounded-xl border-2 border-slate-700 bg-white/5 py-3 px-4 text-white placeholder-slate-500 outline-none transition-all duration-200 focus:border-primary-500 focus:bg-primary-500/5"
                placeholder="حداقل ۸ کاراکتر"
                {{ $isLoading ? 'disabled' : '' }}
            />
            @error('newPassword')
                <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">تکرار رمز عبور جدید</label>
            <input
                type="password"
                wire:model="newPasswordConfirmation"
                class="w-full rounded-xl border-2 border-slate-700 bg-white/5 py-3 px-4 text-white placeholder-slate-500 outline-none transition-all duration-200 focus:border-primary-500 focus:bg-primary-500/5"
                placeholder="رمز عبور جدید را مجدداً وارد کنید"
                {{ $isLoading ? 'disabled' : '' }}
            />
            @error('newPasswordConfirmation')
                <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="btn-primary w-full py-3 px-4 rounded-xl text-base font-semibold text-white flex items-center justify-center gap-2"
            {{ $isLoading ? 'disabled' : '' }}
        >
            @if($isLoading)
                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <span>در حال ذخیره...</span>
            @else
                <span>ذخیره رمز عبور جدید</span>
            @endif
        </button>
    </form>
</div>