<div x-data="{ logoUpdated: false }" @logo-updated.window="logoUpdated = !logoUpdated">
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

    <div class="flex items-start gap-6">
        {{-- Current Logo --}}
        <div class="shrink-0">
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">لوگوی فعلی</p>
            <img
                src="{{ $this->logo_url }}"
                alt="لوگو"
                class="h-16 w-16 rounded-xl object-contain border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-1"
                x-bind:src="logoUpdated ? '{{ $this->logo_url }}?t=' + Date.now() : '{{ $this->logo_url }}'"
            />
        </div>

        {{-- Upload Form --}}
        <div class="flex-1">
            <form wire:submit="uploadLogo" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">
                        آپلود لوگوی جدید
                    </label>
                    <input
                        type="file"
                        wire:model="logo"
                        accept="image/*"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-600 hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-400"
                    />
                    <p class="mt-1 text-xs text-slate-500">PNG, JPG, SVG یا WebP (حداکثر 4MB) - ابعاد پیشنهادی: 256x256 پیکسل</p>
                </div>

                @error('logo')
                    <p class="text-sm text-rose-500 mb-3">{{ $message }}</p>
                @enderror

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="rounded-xl bg-primary-500 px-4 py-2 text-sm font-medium text-white shadow-md shadow-primary-500/30 transition hover:brightness-110"
                        {{ $isLoading ? 'disabled' : '' }}
                    >
                        @if($isLoading)
                            <span>در حال آپلود...</span>
                        @else
                            ذخیره لوگو
                        @endif
                    </button>

                    <button
                        type="button"
                        wire:click="resetLogo"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-800"
                    >
                        بازگشت به پیش‌فرض
                    </button>
                </div>
            </form>

            {{-- Logo Preview --}}
            <div class="mt-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <p class="text-xs text-slate-500 mb-2">پیش‌نمایش در نوار کناری:</p>
                <div class="flex items-center gap-3">
                    <img
                        src="{{ $this->logo_url }}"
                        alt="پیش‌نمایش"
                        class="h-8 w-8 rounded-lg object-contain"
                        x-bind:src="logoUpdated ? '{{ $this->logo_url }}?t=' + Date.now() : '{{ $this->logo_url }}'"
                    />
                    <div>
                        <p class="text-sm font-bold gradient-text">پنل ادمین</p>
                        <p class="text-xs text-slate-500">{{ config('app.name') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>