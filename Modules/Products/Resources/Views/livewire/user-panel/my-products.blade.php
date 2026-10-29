<div>
    {{-- ===== سربرگ + دکمه سفارش جدید — الگوی my-consultations ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">محصولات من</h2>
        <a href="{{ route('public.products.index') }}" class="btn-primary !px-5 !py-2.5 !text-xs">
            <x-icon name="plus" size="4" />
            سفارش جدید
        </a>
    </div>
    {{-- کارت‌های آمار --}}
    <div class="grid gap-4 mb-8 sm:grid-cols-3">
        <div class="p-5 glass-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">محصولات خریداری‌شده</p>
            <p class="mt-1 text-2xl font-black gradient-text">{{ fa_digits($licenses->total()) }}</p>
        </div>
        <div class="p-5 glass-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">لایسنس‌های معتبر</p>
            <p class="mt-1 text-2xl font-black text-emerald-500">{{ fa_digits($validLicenses) }}</p>
        </div>
        <div class="p-5 glass-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">دانلودهای شما</p>
            <p class="mt-1 text-2xl font-black text-primary-600 dark:text-primary-400">{{ fa_digits($totalDownloads) }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="flex items-center gap-2 p-4 mb-6 text-sm font-bold border rounded-2xl border-emerald-300/60 bg-emerald-50 text-emerald-600 dark:border-emerald-700/40 dark:bg-emerald-950/40 dark:text-emerald-400">
            <x-icon name="check" size="4" />
            {{ session('success') }}
        </div>
    @endif

    @forelse ($licenses as $license)
        <div class="p-6 mb-5 glass-card animate-slide-up" wire:key="my-license-{{ $license->id }}">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start">
                <div class="flex items-center justify-center w-16 h-16 shrink-0 rounded-2xl bg-gradient-to-br from-primary-500/10 to-violet-500/10">
                    <x-icon :name="$license->product?->typeEnum()->icon() ?? 'products'" size="7" class="text-primary-500 dark:text-primary-400" />
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ $license->product?->name }}
                        </h3>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold
                            {{ $license->type === 'lifetime' ? 'bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400' : ($license->type === 'multi' ? 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300') }}">
                            {{ $license->type_label }}
                        </span>
                        @if ($license->is_valid)
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">فعال</span>
                        @else
                            <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-700 dark:bg-rose-950/60 dark:text-rose-400">منقضی/استفاده‌شده</span>
                        @endif
                    </div>

                    @if ($license->product?->category)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $license->product->category->name }} · نسخه {{ $license->product->version }}
                        </p>
                    @endif

                    <div class="mt-4">
                        <label class="mb-1.5 block text-xs font-bold text-slate-500 dark:text-slate-400">کلید لایسنس</label>
                        <div class="flex flex-wrap items-center gap-2">
                            <code class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200"
                                  dir="ltr">
                                {{ $license->license_key }}
                            </code>
                            <button type="button"
                                    x-data="{ copied: false }"
                                    x-on:click="navigator.clipboard.writeText('{{ $license->license_key }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="btn-outline !px-4 !py-2.5 !text-xs">
                                <span x-show="!copied">کپی کلید</span>
                                <span x-show="copied" x-cloak class="text-emerald-600 dark:text-emerald-400">✓ کپی شد</span>
                            </button>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-400">
                            فعال‌سازی: {{ fa_digits($license->activations_count) }} از {{ fa_digits($license->max_activations) }} ·
                            @if ($license->expires_at)
                                انقضا: {{ fa_digits($license->expires_at->format('Y/m/d')) }}
                            @else
                                بدون انقضا (مادام‌العمر)
                            @endif
                        </p>
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex flex-wrap gap-3 mt-5">
                        @if ($license->download_url)
                            <a href="{{ $license->download_url }}" class="btn-primary !px-5 !py-2.5 !text-xs">
                                <x-icon name="download" size="4" />
                                دانلود محصول
                            </a>
                        @else
                            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-xs font-semibold text-slate-400 dark:border-slate-700">
                                <x-icon name="download" size="4" />
                                فایل دانلودی موجود نیست
                            </span>
                        @endif

                        @if ($license->product?->demo_url)
                            <a href="{{ $license->product->demo_url }}" target="_blank" rel="noopener" class="btn-outline !px-5 !py-2.5 !text-xs">
                                <x-icon name="eye" size="4" />
                                دموی آنلاین
                            </a>
                        @endif

                        @if ($license->orderItem?->order)
                            <a href="{{ route('user.orders.show', $license->orderItem->order->order_number) }}"
                               class="btn-outline !px-5 !py-2.5 !text-xs">
                                <x-icon name="orders" size="4" />
                                مشاهده سفارش
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="p-12 text-center glass-card">
            <span class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                <x-icon name="products" size="8" />
            </span>
            <h3 class="font-bold text-slate-900 dark:text-white">هنوز محصولی خریداری نکرده‌اید</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">پس از اولین خرید، محصولات و لایسنس‌های شما اینجا نمایش داده می‌شوند.</p>
            <a href="{{ route('public.products.index') }}" class="btn-primary mt-6 !px-5 !py-2.5 !text-xs">
                <x-icon name="products" size="4" />
                مشاهده محصولات
            </a>
        </div>
    @endforelse

    @if ($licenses->hasPages())
        <div class="flex justify-center mt-8">
            {{ $licenses->links('pagination::tailwind-rtl') }}
        </div>
    @endif
</div>
