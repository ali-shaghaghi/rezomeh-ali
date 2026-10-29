<div>
    {{-- هدر صفحه --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت محصولات</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $products->total() }} محصول ثبت‌شده</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.categories') }}"
               class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-400">
                <x-icon name="filter" size="4" />
                دسته‌بندی‌ها
            </a>
            @can('create', \Modules\Products\Models\Product::class)
                <button wire:click="openCreateForm"
                        class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-primary-500/35 active:translate-y-0">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-white/20 transition duration-300 group-hover:rotate-90">
                        <x-icon name="plus" size="3" />
                    </span>
                    افزودن محصول
                </button>
            @endcan
        </div>
    </div>

    {{-- کارت‌های آمار --}}
    <div class="mb-8 grid animate-slide-up gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">کل محصولات</p>
            <p class="text-2xl font-bold gradient-text">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">منتشرشده</p>
            <p class="text-2xl font-bold text-emerald-500">{{ number_format($stats['published']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">قالب‌ها</p>
            <p class="text-2xl font-bold text-violet-500">{{ number_format($stats['templates']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">دسته‌بندی‌ها</p>
            <p class="text-2xl font-bold text-sky-500">{{ number_format($stats['categories']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">در رزومه</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($stats['resume']) }}</p>
        </div>
    </div>

    {{-- جستجو و فیلتر --}}
    <div class="chart-card mb-6 animate-slide-up">
        <div class="flex flex-wrap gap-3">
            <div class="relative min-w-[200px] flex-1">
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی نام یا slug..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-10 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
            </div>
            <select wire:model.live="type" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه انواع</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="category" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه دسته‌ها</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="published" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="1">منتشرشده</option>
                <option value="0">منتشرنشده</option>
            </select>
            @if ($search || $type || $category || $published)
                <button wire:click="resetFilters" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                    حذف فیلترها
                </button>
            @endif
        </div>
    </div>

    {{-- نوتیفیکیشن موفقیت --}}
    @if ($successToast)
        <div class="mb-6 animate-slide-up rounded-xl bg-emerald-100 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            {{ $successMessage }}
        </div>
    @endif

    {{-- گرید محصولات --}}
    <div class="grid animate-slide-up gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($products as $product)
            <article class="chart-card group flex flex-col p-5" wire:key="admin-product-{{ $product->id }}">
                {{-- عکس شاخص محصول --}}
                <div class="mb-3 overflow-hidden rounded-xl border border-slate-200/70 bg-slate-100 dark:border-slate-700/60 dark:bg-surface-800">
                    @if ($product->cover_image)
                        <img src="{{ $product->cover_url }}" alt="{{ $product->name }}" class="object-cover w-full h-28 transition duration-300 group-hover:scale-[1.02]" loading="lazy">
                    @else
                        <div class="flex items-center justify-center h-16">
                            <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500">
                                <x-icon name="eye" size="3.5" />
                                بدون عکس شاخص
                            </span>
                        </div>
                    @endif
                </div>

                <div class="mb-3 flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                                <x-icon :name="$product->typeEnum()->icon()" size="4" />
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500" dir="ltr">{{ $product->slug }}</span>
                        </div>
                        <h3 class="truncate font-bold">{{ $product->name }}</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $product->category?->name ?? 'بدون دسته' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $product->is_published
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                        {{ $product->is_published ? 'منتشر' : 'پیش‌نویس' }}
                    </span>
                </div>

                <div class="mb-4 flex items-center justify-between">
                    <p class="font-black text-primary-600 dark:text-primary-400" dir="ltr">
                        {{ number_format((float) $product->price) }}
                        @if ($product->has_sale)
                            <span class="mr-1 text-xs font-normal text-slate-400 line-through">{{ number_format((float) $product->price) }}</span>
                        @endif
                    </p>
                    <span class="text-xs text-slate-400" dir="ltr">v{{ $product->version }}</span>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-1.5">
                    <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ $product->type_label }}
                    </span>
                    @if ($product->show_in_resume)
                        <span class="rounded-lg bg-violet-100 px-2 py-1 text-xs text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">در رزومه</span>
                    @endif
                    @if ($product->is_downloadable)
                        <span class="rounded-lg bg-sky-100 px-2 py-1 text-xs text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">دانلودی</span>
                    @endif
                </div>

                {{-- عملیات --}}
                <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <button wire:click="openEditForm({{ $product->id }})"
                            class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-400">
                        <x-icon name="edit" size="3.5" />
                        ویرایش
                    </button>
                    <button wire:click="togglePublished({{ $product->id }})"
                            class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-400">
                        <x-icon name="eye" size="3.5" />
                        {{ $product->is_published ? 'لغو انتشار' : 'انتشار' }}
                    </button>
                    <button wire:click="toggleResume({{ $product->id }})"
                            class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-400">
                        <x-icon name="star" size="3.5" />
                        رزومه
                    </button>
                    <button wire:click="confirmDelete({{ $product->id }})"
                            class="mr-auto flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-50 dark:border-rose-800 dark:text-rose-400 dark:hover:bg-rose-950/40">
                        <x-icon name="trash" size="3.5" />
                    </button>
                </div>
            </article>
        @empty
            <div class="chart-card col-span-full p-10 text-center">
                <p class="text-sm text-slate-500 dark:text-slate-400">محصولی یافت نشد.</p>
                <button wire:click="openCreateForm" class="mt-4 rounded-xl bg-primary-500 px-4 py-2 text-xs font-semibold text-white">
                    افزودن اولین محصول
                </button>
            </div>
        @endforelse
    </div>

    @if ($products->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $products->links() }}
        </div>
    @endif

    {{-- مودال ایجاد/ویرایش محصول --}}
    @if ($showForm)
        @include('products::livewire.admin.partials.product-form')
    @endif

    {{-- مودال تایید حذف --}}
    @if ($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" wire:key="delete-modal">
            <div class="w-full max-w-md animate-slide-up rounded-2xl bg-white p-6 shadow-2xl dark:bg-surface-800">
                <h3 class="mb-2 text-lg font-bold">حذف محصول</h3>
                <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">آیا از حذف این محصول مطمئن هستید؟ این عملیات قابل بازگشت نیست.</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('confirmingDelete', false)" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        انصراف
                    </button>
                    <button wire:click="deleteProduct" wire:loading.attr="disabled" wire:target="deleteProduct"
                            class="rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white">
                        <span wire:loading.remove wire:target="deleteProduct">حذف محصول</span>
                        <span wire:loading wire:target="deleteProduct">در حال حذف...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
