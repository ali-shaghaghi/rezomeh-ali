<div class="space-y-4">
    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">عنوان سئو ({{ mb_strlen($title) }}/60)</label>
        <input type="text" wire:model.live="title" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800" placeholder="عنوان جذاب با کلمه کلیدی اصلی">
        @error('title') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        @if (mb_strlen($title) > 60)
            <p class="mt-1 text-xs font-bold text-amber-500">⚠️ بیش از ۶۰ کاراکتر — در گوگل بریده می‌شود</p>
        @endif
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">توضیحات متا ({{ mb_strlen($description) }}/160)</label>
        <textarea wire:model.live="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800" placeholder="توضیح کوتاه و جذاب که کاربر در نتایج جستجو می‌بیند"></textarea>
        @if (mb_strlen($description) > 160)
            <p class="mt-1 text-xs font-bold text-amber-500">⚠️ بیش از ۱۶۰ کاراکتر — بریده می‌شود</p>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">کلمات کلیدی (با کاما)</label>
            <input type="text" wire:model="keywords" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800" placeholder="کلمه ۱, کلمه ۲, کلمه ۳">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">نوع اسکیما (Schema)</label>
            <select wire:model="schema_type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800">
                @foreach (['WebPage', 'Product', 'Service', 'ProfessionalService', 'Article', 'BlogPosting', 'ItemList', 'CollectionPage', 'FAQPage', 'LocalBusiness'] as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">تصویر OG (مسیر در storage)</label>
        <input type="text" wire:model="og_image" dir="ltr" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-surface-800" placeholder="seo/og-home.jpg">
    </div>

    {{-- FAQ — برای AI search --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">سوالات متداول (FAQ Schema — برای پیشنهاد در AI)</p>
            <button type="button" wire:click="addFaq" class="rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-bold text-white">+ سوال</button>
        </div>
        <div class="space-y-3">
            @foreach ($faq as $i => $item)
                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60" wire:key="faq-{{ $i }}">
                    <input type="text" wire:model.live="faq.{{ $i }}.q" placeholder="سوال {{ fa_digits($i + 1) }}؟"
                           class="mb-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-surface-800">
                    <textarea wire:model.live="faq.{{ $i }}.a" rows="2" placeholder="پاسخ کوتاه و دقیق (مناسب Featured Snippet)"
                              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-surface-800"></textarea>
                    <button type="button" wire:click="removeFaq({{ $i }})" class="mt-1.5 text-[10px] font-bold text-rose-500 hover:underline">حذف</button>
                </div>
            @endforeach
        </div>
    </div>

    <label class="flex cursor-pointer items-center gap-2.5">
        <input type="checkbox" wire:model="noindex" class="h-5 w-5 rounded-lg border-slate-300 text-rose-600">
        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">noindex — این صفحه از گوگل مخفی شود</span>
    </label>

    <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
        <button type="button" wire:click="$set('modalOpen', false)" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 dark:border-slate-700 dark:text-slate-300">انصراف</button>
        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25">
            ذخیره متاها
        </button>
    </div>
</div>
