{{-- گالری تصاویر — آپلود / ویرایش سئو / انتشار / حذف (Livewire) --}}
<div class="space-y-4" x-data="{ editModalOpen: false, deleteModalOpen: false, toastMsg: '', toastOpen: false }">

    {{-- نوتیفیکیشن --}}
    <div x-show="toastOpen" x-cloak x-transition.opacity
         x-on:gallery-notify.window="toastMsg = $event.detail.message || ''; toastOpen = true; clearTimeout(window.__galToast); window.__galToast = setTimeout(() => toastOpen = false, 2600)"
         class="fixed z-50 px-5 py-3 text-sm font-semibold text-white -translate-x-1/2 shadow-2xl bottom-6 left-1/2 rounded-xl bg-slate-900 dark:bg-white dark:text-slate-900">
        <span x-text="toastMsg"></span>
    </div>

    {{-- هدر --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        
        <span class="px-3 py-1 text-xs font-bold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300">
            {{ fa_digits($total) }} تصویر
        </span>
    </div>

    {{-- ناحیه آپلود --}}
    <label for="gallery-uploads"
           class="flex flex-col items-center justify-center gap-2 px-4 py-8 text-center transition border-2 border-dashed cursor-pointer rounded-2xl border-slate-300 bg-slate-50/60 hover:border-primary-400 hover:bg-primary-50/40 dark:border-slate-700 dark:bg-surface-800/60 dark:hover:border-primary-500"
           ondragover="event.preventDefault(); this.classList.add('border-primary-500')"
           ondragleave="this.classList.remove('border-primary-500')"
           ondrop="event.preventDefault(); this.classList.remove('border-primary-500')">
        <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">تصویر را اینجا بکشید یا برای انتخاب کلیک کنید</p>
        <p class="text-xs text-slate-400 dark:text-slate-500">JPG، PNG، WebP تا ۸ مگابایت — حداکثر ۱۲ فایل هم‌زمان</p>
        <input id="gallery-uploads" type="file" accept="image/*" multiple
               wire:model="uploads" class="sr-only">

        <div wire:loading wire:target="uploads" class="w-full max-w-xs pt-2">
            <div class="w-full h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                <div class="w-1/3 h-full rounded-full animate-pulse bg-primary-500"></div>
            </div>
            <p class="mt-1 text-[11px] font-bold text-primary-600">در حال آپلود…</p>
        </div>

        @error('uploads.*')
            <p class="text-[11px] font-bold text-rose-600">{{ $message }}</p>
        @enderror
    </label>

    {{-- فیلترها --}}
    <div class="flex flex-wrap items-center gap-3 p-3 bg-white border rounded-xl border-slate-200 dark:border-slate-700 dark:bg-surface-800">
        <div class="relative flex-1 min-w-[200px] max-w-xs">
            <svg class="absolute w-4 h-4 -translate-y-1/2 left-3 top-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجو در عنوان/توضیح…"
                   class="w-full py-2 pr-3 text-sm bg-white border rounded-lg border-slate-200 pl-9 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-900 dark:text-slate-100">
        </div>

        <select wire:model.live="filterType"
                class="px-3 py-2 text-sm bg-white border rounded-lg border-slate-200 focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-900 dark:text-slate-100">
            <option value="all">همه</option>
            <option value="image">عکس</option>
            <option value="video">ویدیو</option>
        </select>

        <label class="flex items-center gap-2 text-sm cursor-pointer select-none text-slate-600 dark:text-slate-300">
            <input type="checkbox" wire:model.live="showUnlisted" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20">
            نمایش غیرمنتشرشده‌ها
        </label>
    </div>

    {{-- شبکه تصاویر --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
        @foreach ($assets as $asset)
            <div class="relative overflow-hidden transition bg-white border group rounded-2xl border-slate-200 hover:shadow-lg dark:border-slate-700 dark:bg-surface-800">

                {{-- تصویر --}}
                <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-surface-900">
                    @if ($asset->srcAttribute())
                        <img src="{{ $asset->srcAttribute() }}" alt="{{ $asset->alt ?: $asset->meta_title ?: '' }}"
                             class="object-cover w-full h-full transition group-hover:scale-105" loading="lazy">
                    @else
                        <div class="flex items-center justify-center h-full text-xs font-bold text-slate-400">بدون لینک عمومی</div>
                    @endif

                    {{-- نشان انتشار --}}
                    <span class="absolute top-2 right-2 rounded-lg px-2 py-0.5 text-[10px] font-bold shadow
                        {{ $asset->public_hash ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300' }}">
                        {{ $asset->public_hash ? 'منتشر' : 'غیرمنتشر' }}
                    </span>

                    {{-- عملیات هاور --}}
                    <div class="absolute inset-x-0 bottom-0 flex items-end gap-1.5 bg-gradient-to-t from-black/70 via-black/30 to-transparent p-2 opacity-0 transition group-hover:opacity-100 focus-within:opacity-100">
                        <button type="button" wire:click="startEdit({{ $asset->id }})" x-on:click="editModalOpen = true"
                                class="rounded-lg bg-white/95 px-2.5 py-1 text-[11px] font-bold text-slate-800 transition hover:bg-white">ویرایش</button>

                        @if ($asset->public_hash)
                            <button type="button"
                                    x-on:click="navigator.clipboard.writeText('{{ $asset->srcAttribute() }}'); $dispatch('gallery-notify', { message: 'لینک کپی شد' })"
                                    class="rounded-lg bg-white/95 px-2.5 py-1 text-[11px] font-bold text-slate-800 transition hover:bg-white">کپی لینک</button>
                        @endif

                        <button type="button" wire:click="togglePublish({{ $asset->id }})"
                                class="rounded-lg bg-white/95 px-2.5 py-1 text-[11px] font-bold text-slate-800 transition hover:bg-white">
                            {{ $asset->public_hash ? 'لغو انتشار' : 'انتشار' }}
                        </button>

                        <button type="button" wire:click="confirmDelete({{ $asset->id }})" x-on:click="deleteModalOpen = true"
                                class="rounded-lg bg-rose-600/95 px-2.5 py-1 text-[11px] font-bold text-white transition hover:bg-rose-600">حذف</button>
                    </div>
                </div>

                {{-- متادیتا --}}
                <div class="space-y-0.5 p-3">
                    <p class="text-sm font-bold truncate text-slate-800 dark:text-slate-100">{{ $asset->alt ?: $asset->meta_title ?: $asset->slug }}</p>
                    <p class="text-xs truncate text-slate-400">{{ $asset->meta_title ?: '—' }}</p>
                    <p class="flex items-center gap-2 text-[11px] text-slate-400">
                        @if ($asset->width && $asset->height)
                            <span>{{ fa_digits($asset->width) }}×{{ fa_digits($asset->height) }}</span>
                        @endif
                        @if ($asset->size_bytes)
                            <span>{{ fa_digits(number_format($asset->size_bytes / 1024)) }} KB</span>
                        @endif
                        <span>{{ fa_digits($asset->created_at?->format('Y/m/d')) }}</span>
                    </p>
                </div>
            </div>
        @endforeach

        @if ($assets->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 border-2 border-dashed col-span-full rounded-2xl border-slate-200 text-slate-400 dark:border-slate-700">
                <svg class="w-12 h-12 mb-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <p class="text-lg font-medium">تصویری یافت نشد.</p>
                <p class="mt-1 text-sm">با ناحیهٔ آپلود بالا اولین تصویر خود را اضافه کنید.</p>
            </div>
        @endif
    </div>

    {{-- صفحه‌بندی --}}
    @if ($assets->hasPages())
        <div class="flex items-center justify-center gap-3">
            <button wire:click="previousPage" wire:loading.attr="disabled" wire:target="previousPage"
                    @disabled(! $assets->onFirstPage())
                    class="px-4 py-2 text-sm font-medium transition bg-white border rounded-lg border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 dark:border-slate-700 dark:bg-surface-800 dark:text-slate-300">
                قبلی
            </button>
            <span class="text-sm text-slate-500 dark:text-slate-400">صفحهٔ {{ fa_digits($assets->currentPage()) }} از {{ fa_digits($assets->lastPage()) }}</span>
            <button wire:click="nextPage" wire:loading.attr="disabled" wire:target="nextPage"
                    @disabled(! $assets->hasMorePages())
                    class="px-4 py-2 text-sm font-medium transition bg-white border rounded-lg border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 dark:border-slate-700 dark:bg-surface-800 dark:text-slate-300">
                بعدی
            </button>
        </div>
    @endif

    {{-- مودال ویرایش سئو --}}
    <div x-show="editModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="$wire.cancelEdit()">
        <div x-transition.scale.95 class="w-full max-w-lg p-6 bg-white shadow-2xl rounded-2xl dark:bg-surface-800">
            <h3 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">ویرایش اطلاعات سئوی تصویر</h3>

            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">متن جایگزین (Alt)</label>
                    <input type="text" wire:model="editAlt" maxlength="255"
                           placeholder="توصیف تصویر برای موتور جستجو و افراد کم‌بینا"
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-900 dark:text-slate-100">
                    @error('editAlt') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">عنوان سئو (Title)</label>
                    <input type="text" wire:model="editTitle" maxlength="255"
                           placeholder="عنوانی که در نتایج جستجو دیده می‌شود"
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-900 dark:text-slate-100">
                    @error('editTitle') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">توضیح سئو (Description)</label>
                    <textarea wire:model="editDescription" rows="2" maxlength="1000"
                              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-900 dark:text-slate-100"></textarea>
                    @error('editDescription') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm cursor-pointer text-slate-600 dark:text-slate-300">
                    <input type="checkbox" wire:model="editPublished" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20">
                    منتشر شود (لینک هش‌شدهٔ عمومی فعال شود)
                </label>
            </div>

            <div class="flex items-center gap-3 mt-6">
                <button wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit" x-on:click="editModalOpen = false"
                        class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-primary-600 disabled:opacity-50">
                    ذخیره
                </button>
                <button wire:click="cancelEdit" x-on:click="editModalOpen = false"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300">
                    انصراف
                </button>
            </div>
        </div>
    </div>

    {{-- مودال تأیید حذف --}}
    <div x-show="deleteModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="$wire.cancelDelete()">
        <div x-transition.scale.95 class="w-full max-w-sm p-6 text-center bg-white shadow-2xl rounded-2xl dark:bg-surface-800">
            <div class="flex items-center justify-center mx-auto mb-4 rounded-full h-14 w-14 bg-rose-100 dark:bg-rose-900/40">
                <svg class="h-7 w-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">حذف تصویر؟</h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">فایل و رکورد آن برای همیشه حذف می‌شود و لینک هش‌شده از کار می‌افتد.</p>

            <div class="flex items-center gap-3 mt-6">
                <button wire:click="destroyAsset" wire:loading.attr="disabled" wire:target="destroyAsset" x-on:click="deleteModalOpen = false"
                        class="flex-1 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-rose-700 disabled:opacity-50">
                    بله، حذف شود
                </button>
                <button wire:click="cancelDelete" x-on:click="deleteModalOpen = false"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300">
                    انصراف
                </button>
            </div>
        </div>
    </div>
</div>
