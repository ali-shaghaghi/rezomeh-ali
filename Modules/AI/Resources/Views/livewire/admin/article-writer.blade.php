<div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">موضوع مقاله *</label>
            <input type="text" wire:model="topic" placeholder="مثال: ۵ نکته طلایی قبل از سفارش CRM اختصاصی"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800">
            @error('topic') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">کلمات کلیدی هدف (اختیاری)</label>
            <input type="text" wire:model="keywords" placeholder="ساخت CRM, قیمت نرم افزار, ..."
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800">
        </div>
    </div>

    <div class="mt-3 grid gap-4 sm:grid-cols-3">
        <div>
            <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">لحن</label>
            <select wire:model="tone" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800">
                <option value="professional">حرفه‌ای و رسمی</option>
                <option value="friendly">دوستانه</option>
                <option value="educational">آموزشی</option>
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">زبان خروجی</label>
            <select wire:model="language" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800">
                <option value="both">فارسی + انگلیسی</option>
                <option value="fa">فقط فارسی</option>
                <option value="en">فقط انگلیسی</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                    class="w-full rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-500/25 transition hover:bg-primary-600 disabled:opacity-50">
                <span wire:loading.remove wire:target="generate">✨ تولید مقاله</span>
                <span wire:loading wire:target="generate">در حال تولید...</span>
            </button>
        </div>
    </div>

    @if ($errorMessage)
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-bold text-rose-600 dark:border-rose-800 dark:bg-rose-950/30 dark:text-rose-400">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($result)
        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-800 dark:bg-emerald-950/20">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400">خروجی JSON آماده ذخیره</p>
                <button type="button" wire:click="saveAsDraft" wire:loading.attr="disabled" wire:target="saveAsDraft"
                        class="rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white shadow-md shadow-emerald-500/25 transition hover:bg-emerald-600 disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveAsDraft">💾 ذخیره به‌عنوان پیش‌نویس وبلاگ</span>
                    <span wire:loading wire:target="saveAsDraft">در حال ذخیره...</span>
                </button>
            </div>
            <pre class="max-h-64 overflow-auto rounded-xl bg-white/80 p-3 text-[10px] leading-5 text-slate-700 dark:bg-slate-900/60 dark:text-slate-300" dir="ltr">{{ $result }}</pre>
        </div>
    @endif
</div>
