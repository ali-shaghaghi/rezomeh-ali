    {{-- ===== انتشار ===== --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
        <p class="mb-3 text-sm font-bold text-slate-700 dark:text-slate-200">۲. انتشار محتوا / کمپین تبلیغاتی</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">نوع محتوا</label>
                <select wire:model.live="contentType" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800">
                    <option value="latest_post">📰 مقاله جدید (آخرین)</option>
                    <option value="latest_product">🛍️ محصول جدید (آخرین)</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">انتخاب مشخص (اختیاری)</label>
                <select wire:model="selectedId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800">
                    <option value="0">— آخرین خودکار —</option>
                    @if ($contentType === 'latest_post')
                        @foreach ($posts as $post)
                            <option value="{{ $post->id }}">{{ \Illuminate\Support\Str::limit($post->title, 50) }}</option>
                        @endforeach
                    @else
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ \Illuminate\Support\Str::limit($product->name, 50) }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button" wire:click="generateCaption" wire:loading.attr="disabled" wire:target="generateCaption"
                    class="rounded-xl bg-primary-500 px-4 py-2 text-xs font-bold text-white shadow-md shadow-primary-500/25 transition hover:bg-primary-600 disabled:opacity-50">
                ✨ تولید کپشن تبلیغاتی با AI
            </button>
            <button type="button" wire:click="publish" wire:loading.attr="disabled" wire:target="publish"
                    class="rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white shadow-md shadow-emerald-500/25 transition hover:bg-emerald-600 disabled:opacity-50">
                <span wire:loading.remove wire:target="publish">🚀 انتشار در تلگرام</span>
                <span wire:loading wire:target="publish">در حال ارسال...</span>
            </button>
        </div>

        @if ($caption)
            <textarea wire:model="caption" rows="5"
                      class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs leading-6 dark:border-slate-600 dark:bg-slate-900/60 dark:text-slate-200"
                      placeholder="کپشن — قابل ویرایش قبل از انتشار"></textarea>
        @endif

        @if ($successMessage)
            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold text-emerald-600 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">{{ $successMessage }}</div>
        @endif

        @if ($errorMessage)
            <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-bold text-rose-600 dark:border-rose-800 dark:bg-rose-950/30 dark:text-rose-400">{{ $errorMessage }}</div>
        @endif
    </div>
