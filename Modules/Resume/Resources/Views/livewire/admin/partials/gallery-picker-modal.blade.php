{{-- مودال انتخاب تصویر از گالری — باز شدن ریل‌تایم (Alpine) بدون رفرش صفحه --}}
{{-- نیازمندی‌های scope والد: pickerOpen, pick, assets, confirmPick() --}}
<div x-cloak x-show="pickerOpen" x-transition.opacity
     class="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
     @keydown.escape.window="pickerOpen = false">
    <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-surface-800" @click.stop>
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700">
            <div>
                <h4 class="text-base font-bold">انتخاب تصویر از گالری</h4>
                <p class="mt-0.5 text-[11px] text-slate-400">تصویر موردنظر را انتخاب کنید و دکمهٔ «انتخاب تصویر» را بزنید</p>
            </div>
            <button type="button" @click="pickerOpen = false" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="بستن">
                <x-icon name="close" size="5" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-3 gap-3">
                <template x-for="asset in assets" :key="asset.id">
                    <button type="button" @click="pick = asset"
                            class="group relative overflow-hidden rounded-xl border-2 text-start transition"
                            :class="pick && pick.id === asset.id ? 'border-primary-500 ring-2 ring-primary-500/30' : 'border-transparent hover:border-primary-300'">
                        <img :src="asset.thumb" loading="lazy" class="h-24 w-full object-cover" :alt="asset.alt || ''">
                        <span class="block truncate px-2 py-1 text-[10px] text-slate-600 dark:text-slate-300" x-text="asset.alt || 'بدون عنوان'"></span>
                        <span x-show="pick && pick.id === asset.id" x-cloak
                              class="absolute top-1.5 left-1.5 rounded-full bg-primary-500 px-1.5 py-0.5 text-[9px] font-bold text-white shadow">✓</span>
                    </button>
                </template>
                <div x-show="!assets.length" class="col-span-3 py-12 text-center text-sm text-slate-400">
                    هنوز تصویری در گالری نیست — ابتدا از بخش «گالری تصاویر» در پنل آپلود کنید.
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 border-t border-slate-100 px-5 py-3 dark:border-slate-700">
            <span class="hidden text-[11px] text-slate-400 sm:inline" x-show="pick" x-text="pick && pick.alt ? 'Alt پیش‌فرض: ' + pick.alt : ''"></span>
            <div class="ms-auto flex gap-2">
                <button type="button" @click="pickerOpen = false"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                    بستن
                </button>
                <button type="button" @click="confirmPick()" :disabled="!pick"
                        class="rounded-xl bg-primary-500 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-40">
                    انتخاب تصویر
                </button>
            </div>
        </div>
    </div>
</div>
