{{-- نسخه انگلیسی مقاله (اختیاری) — برای سئو نسخه خارجی و کاربران انگلیسی‌زبان --}}
<div class="p-6 chart-card">
    <div class="flex items-center gap-2 mb-4">
        <x-flag code="en" class="w-5 h-[13px] shrink-0" />
        <h3 class="text-lg font-bold">English version</h3>
        <span class="text-xs font-medium text-slate-400 dark:text-slate-500">(اختیاری)</span>
    </div>
    <div class="space-y-4">
        <div>
            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">Title (EN)</label>
            <input type="text" wire:model.blur="title_en" dir="ltr" lang="en"
                   class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white"
                   placeholder="Article title in English" />
            @error('title_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">Excerpt (EN)</label>
            <input type="text" wire:model.blur="excerpt_en" dir="ltr" lang="en"
                   class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white"
                   placeholder="One-line summary for cards and SEO" />
            @error('excerpt_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">Content (EN)</label>
            <x-rich-editor target="content_en" upload-prop="inlineUploadEn" event="inline-media-uploaded-en" lang="en" :gallery-assets="$galleryAssets" />
            @error('content_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60">
            <p class="mb-3 text-xs font-bold text-slate-400 dark:text-slate-500">English SEO</p>
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">SEO Title (EN)</label>
                    <input type="text" wire:model.blur="seo_title_en" dir="ltr" lang="en"
                           class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white"
                           placeholder="SEO title in English" />
                    @error('seo_title_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">SEO Description (EN)</label>
                    <textarea wire:model.blur="seo_description_en" dir="ltr" lang="en" rows="2"
                              class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white"
                              placeholder="SEO meta description in English"></textarea>
                    @error('seo_description_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
@if (! empty($post->title_en) || ! empty($post->excerpt_en) || ! empty($post->content_en))
            <p class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="20 6 9 17 4 12"/></svg>
                English version of this article is complete.
            </p>
        @else
            <p class="text-xs text-slate-400 dark:text-slate-500">If left empty, the version in Persian will be prepared and displayed.</p>
        @endif
    </div>
</div>
