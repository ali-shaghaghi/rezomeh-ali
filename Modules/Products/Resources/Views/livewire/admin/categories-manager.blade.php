<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">دسته‌بندی محصولات</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ fa_digits($stats['total']) }} دسته‌بندی — {{ fa_digits($stats['active']) }} فعال</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.index') }}"
               class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-400">
                <x-icon name="products" size="4" />
                محصولات
            </a>
            <button type="button" wire:click="openCreateModal"
                    class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                افزودن دسته‌بندی
            </button>
        </div>
    </div>

    {{-- جستجو و فیلتر ریاکتیو --}}
    <div class="mb-6 animate-slide-up chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی نام یا اسلاگ..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-10 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            <select wire:model.live="status"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="inactive">غیرفعال</option>
            </select>
            @if($search || $status)
                <button wire:click="resetFilters" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                    حذف فیلترها
                </button>
            @endif
        </div>
    </div>

    {{-- نوتیفیکیشن موفقیت (ریاکتیو) --}}
    @if($successToast)
        <div class="flex items-center justify-between gap-3 px-4 py-3 mb-6 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            <span class="flex items-center gap-2">
                <x-icon name="check" size="4" />
                {{ $successMessage }}
            </span>
        </div>
    @endif
    {{-- ایندیکیتور لودینگ --}}
    <div wire:loading.delay.200ms class="fixed z-40 hidden items-center gap-2 px-4 py-2 text-xs font-medium text-white rounded-full shadow-lg bottom-6 left-6 bg-primary-500/90 backdrop-blur-sm"
         wire:target="search, status, resetFilters, saveCategory, deleteCategory, toggleActive">
        <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        در حال بارگذاری...
    </div>

    {{-- گرید کارت‌های دسته‌بندی — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="grid gap-5 animate-slide-up sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="pointer-events-none opacity-60" wire:target="deleteCategory, toggleActive">
        @forelse($categories as $category)
            <article class="flex flex-col overflow-hidden chart-card group" wire:key="category-card-{{ $category->id }}">
                {{-- سربرگ کارت --}}
                <div class="relative flex items-center justify-center h-28 transition bg-gradient-to-bl from-slate-100 to-slate-50 group-hover:from-primary-50 group-hover:to-violet-50 dark:from-slate-800 dark:to-slate-900 dark:group-hover:from-primary-950/50">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/80 text-primary-500 shadow-sm backdrop-blur-sm dark:bg-slate-900/70 dark:text-primary-400">
                        <x-icon name="filter" size="7" />
                    </span>

                    <span class="absolute top-3 left-3 rounded-full bg-primary-100/90 px-2.5 py-1 text-xs font-bold text-primary-700 backdrop-blur-sm dark:bg-primary-900/70 dark:text-primary-300">
                        {{ fa_digits($category->products_count) }} محصول
                    </span>

                    <span class="absolute top-3 right-3 px-2.5 py-1 text-xs font-medium rounded-full backdrop-blur-sm {{ $category->is_active ? 'bg-emerald-100/90 text-emerald-700 dark:bg-emerald-900/70 dark:text-emerald-300' : 'bg-rose-100/90 text-rose-700 dark:bg-rose-900/70 dark:text-rose-300' }}">
                        {{ $category->is_active ? 'فعال' : 'غیرفعال' }}
                    </span>
                </div>

                <div class="flex flex-col flex-1 p-4">
                    <h3 class="font-bold">{{ $category->name }}</h3>
                    @if ($category->parent)
                        <p class="mt-0.5 text-xs text-slate-400">زیرمجموعه: {{ $category->parent->name }}</p>
                    @endif

                    @if ($category->description)
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{{ $category->description }}</p>
                    @endif

                    <p class="mt-2 text-xs text-slate-400" dir="ltr">{{ $category->slug }}</p>

                    <div class="flex gap-2 mt-4">
                        <button wire:click="openEditModal({{ $category->id }})"
                                class="flex-1 rounded-lg border py-2 text-xs font-medium transition border-slate-200 hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:text-slate-300 dark:hover:text-primary-400">
                            ویرایش
                        </button>
                        <button wire:click="toggleActive({{ $category->id }})"
                                wire:loading.attr="disabled"
                                class="flex-1 rounded-lg border py-2 text-xs font-medium transition hover:border-primary-300 dark:border-slate-600 {{ $category->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300' }}">
                            {{ $category->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}
                        </button>
                        <button wire:click="confirmDelete({{ $category->id }})"
                                class="rounded-lg border px-3 py-2 text-xs font-medium transition border-slate-200 text-rose-500 hover:bg-rose-50 dark:border-slate-600 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                aria-label="حذف دسته‌بندی">
                            <x-icon name="trash" size="3.5" />
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <div class="glass-card col-span-full flex flex-col items-center justify-center gap-4 p-12 text-center animate-fade-in">
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="filter" size="8" />
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">هنوز دسته‌بندی‌ای ایجاد نشده است</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">اولین دسته‌بندی محصولات را بسازید.</p>
                </div>
                <button type="button" wire:click="openCreateModal" class="btn-primary !px-5 !py-2.5 !text-xs">
                    <x-icon name="plus" size="4" />
                    افزودن دسته‌بندی
                </button>
            </div>
        @endforelse
    </div>

    @if ($categories->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $categories->links('pagination::tailwind-rtl') }}
        </div>
    @endif
    {{-- مودال حذف --}}
    @if ($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" wire:key="category-delete-modal">
            <div class="w-full max-w-sm animate-slide-up rounded-2xl bg-white p-6 shadow-2xl dark:bg-surface-800">
                <div class="mb-4 flex items-center gap-2">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">
                        <x-icon name="trash" size="5" />
                    </span>
                    <h3 class="font-bold text-slate-900 dark:text-white">حذف دسته‌بندی</h3>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    آیا از حذف «{{ $deleteName }}» مطمئن هستید؟ محصولات این دسته‌بندی حذف نمی‌شوند اما بدون دسته خواهند شد.
                </p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteCategory" wire:loading.attr="disabled"
                            class="flex-1 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/25 transition hover:bg-rose-600 disabled:opacity-50">
                        بله، حذف شود
                    </button>
                    <button type="button" wire:click="$set('confirmingDelete', false)"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        لغو
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال ایجاد/ویرایش — real-time --}}
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" wire:key="category-form-modal"
             x-data x-on:keydown.escape.window="closeModal">
            <div class="w-full max-w-lg animate-slide-up rounded-2xl bg-white shadow-2xl dark:bg-surface-800">
                <form wire:submit.prevent="saveCategory" class="space-y-4 p-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">نام <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="مثال: قالب‌های وب"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
                        @error('name') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">slug (اختیاری)</label>
                        <input type="text" wire:model="slug" placeholder="auto-generated" dir="ltr"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
                        @error('slug') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">دسته والد</label>
                        <select wire:model="parent_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                            <option value="">— بدون والد —</option>
                            @foreach($parentOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">توضیحات</label>
                        <textarea wire:model="description" rows="3" placeholder="توضیح کوتاه دسته‌بندی..."
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800"></textarea>
                        @error('description') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5">
                        <input type="checkbox" wire:model="is_active" class="h-5 w-5 rounded-lg border-slate-300 text-primary-600 focus:ring-primary-500/20">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">فعال</span>
                    </label>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveCategory"
                                class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="saveCategory">{{ $editing ? 'ذخیره تغییرات' : 'افزودن دسته' }}</span>
                            <span wire:loading wire:target="saveCategory">در حال ذخیره...</span>
                        </button>
                        <button type="button" wire:click="closeModal"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            لغو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
