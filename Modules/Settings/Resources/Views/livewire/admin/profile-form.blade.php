<div>
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

    <form wire:submit="updateProfile" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-400">نام</label>
                <input
                    type="text"
                    wire:model="name"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800"
                    {{ $isLoading ? 'disabled' : '' }}
                />
                @error('name')
                    <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-400">نام خانوادگی</label>
                <input
                    type="text"
                    value="{{ Auth::user()->name ?? '' }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800"
                    disabled
                />
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-400">ایمیل</label>
            <div class="relative">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="mail" />
                </span>
                <input
                    type="email"
                    wire:model="email"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-11 pl-4 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800"
                    {{ $isLoading ? 'disabled' : '' }}
                />
            </div>
            @error('email')
                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-medium text-white shadow-md shadow-primary-500/30 transition hover:brightness-110"
            {{ $isLoading ? 'disabled' : '' }}
        >
            @if($isLoading)
                <span>در حال ذخیره...</span>
            @else
                ذخیره تغییرات
            @endif
        </button>
    </form>
</div>