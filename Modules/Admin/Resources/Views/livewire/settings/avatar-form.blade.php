<div x-data="{ avatarUpdated: false }" @avatar-updated.window="avatarUpdated = !avatarUpdated">
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

    <div class="flex items-center gap-6">
        {{-- Current Avatar --}}
        <div class="relative">
            <img
                src="{{ $this->avatar_url }}"
                alt="آواتار"
                class="h-16 w-16 rounded-xl object-cover border border-slate-200 dark:border-slate-700"
                x-bind:src="avatarUpdated ? '{{ $this->avatar_url }}?t=' + Date.now() : '{{ $this->avatar_url }}'"
            />
            @if(Auth::user()->avatar)
                <button
                    type="button"
                    wire:click="deleteAvatar"
                    class="absolute -top-2 -left-2 h-6 w-6 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs hover:bg-rose-600 transition"
                >
                    <x-icon name="close" size="3" />
                </button>
            @endif
        </div>

        {{-- Upload Form --}}
        <div class="flex-1">
            <form wire:submit="uploadAvatar" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">
                        تصویر پروفایل
                    </label>
                    <input
                        type="file"
                        wire:model.live="avatar"
                        accept="image/*"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-600 hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-400"
                    />
                    <p class="mt-1 text-xs text-slate-500">PNG, JPG, GIF یا SVG (حداکثر 2MB)</p>
                </div>

                @error('avatar')
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
                            آپلود تصویر
                        @endif
                    </button>

                    @if(Auth::user()->avatar)
                        <button
                            type="button"
                            wire:click="deleteAvatar"
                            class="rounded-xl border border-rose-300 px-4 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:border-rose-700 dark:text-rose-400 dark:hover:bg-rose-900/20"
                        >
                            حذف تصویر
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>