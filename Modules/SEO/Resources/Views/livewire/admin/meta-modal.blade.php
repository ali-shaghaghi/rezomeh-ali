{{-- مودال ویرایش متا --}}
@if ($modalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" wire:key="seo-modal"
         @click.self="$set('modalOpen', false)" @keydown.escape.window="$set('modalOpen', false)">
        <div class="max-h-[90vh] w-full max-w-2xl animate-slide-up overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl dark:bg-surface-800">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-lg font-bold">ویرایش سئو — {{ $editRoute }} ({{ $locale === 'fa' ? 'فارسی' : 'English' }})</h3>
                <button type="button" wire:click="$set('modalOpen', false)" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <x-icon name="close" />
                </button>
            </div>

            {{-- پیش‌نمایش SERP گوگل — زنده --}}
            <div class="mb-5 rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                <p class="mb-2 text-[10px] font-bold uppercase text-slate-400">پیش‌نمایش نتیجه گوگل (SERP)</p>
                <p class="text-xs text-emerald-700 dark:text-emerald-400" dir="ltr">https://yoursite.ir › ...</p>
                <p class="mt-0.5 text-lg text-blue-700 dark:text-blue-400">{{ $title ?: 'عنوان صفحه — پیش‌فرض خودکار' }}</p>
                <p class="mt-0.5 text-xs leading-5 text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($description ?: 'توضیحات متا به‌صورت خودکار از محتوای صفحه ساخته می‌شود.', 160) }}</p>
            </div>

            @include('seo::livewire.admin.meta-form')
        </div>
    </div>
@endif
