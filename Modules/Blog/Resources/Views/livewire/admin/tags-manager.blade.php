<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت برچسب‌ها</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $tags->total() }} برچسب ثبت‌شده</p>
        </div>
        @can('create', \Modules\Core\Models\BlogPost::class)
            <button wire:click="openCreateModal"
                    class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                اضافه کردن برچسب
            </button>
        @endcan
    </div>

    {{-- جستجو --}}
    <div class="mb-6 animate-slide-up chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی برچسب..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-10 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            @if($search)
                <button wire:click="resetFilters" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                    حذف فیلترها
                </button>
            @endif
        </div>
    </div>

    {{-- نوتیفیکیشن موفقیت --}}
    @if($successToast)
        <div class="px-4 py-3 mb-6 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            {{ $successMessage }}
        </div>
    @endif

    {{-- گرید برچسب‌ها (الگوی Roles) — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up" wire:loading.class="pointer-events-none opacity-60" wire:target="save, deleteTag">
        @forelse($tags as $tag)
            <section class="flex flex-col chart-card">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="star" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $tag->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $tag->slug }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                        {{ $tag->blog_posts_count }} مقاله
                    </span>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex flex-wrap gap-2 pt-3 mt-auto border-t border-slate-100 dark:border-slate-700">
                    @can('create', \Modules\Core\Models\BlogPost::class)
                        <button wire:click="openEditModal({{ $tag->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            ویرایش
                        </button>
                    @endcan
                    @if(auth()->user()?->hasPermission('manage_blog'))
                        <button wire:click="confirmDelete({{ $tag->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    @endif
                </div>
            </section>
        @empty
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="star" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'برچسبی یافت نشد' : 'هنوز برچسبی ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید' : 'اولین برچسب را اضافه کنید' }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($tags->hasPages())
        <div class="mt-6">
            {{ $tags->links() }}
        </div>
    @endif

    {{-- مودال افزودن/ویرایش (پس‌زمینه بلور + انیمیشن — الگوی Roles) --}}
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm animate-slide-up sm:p-4">
            <div class="w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
                {{-- هدر مودال --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="star" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $editing ? 'ویرایش برچسب: ' . $name : 'اضافه کردن برچسب جدید' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $editing ? 'اطلاعات برچسب را به‌روزرسانی کنید' : 'اطلاعات برچسب را وارد کنید' }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('modalOpen', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-3">
                    {{-- نام و اسلاگ --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام برچسب <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="name" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: Laravel" />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">Slug <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="slug" required dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="laravel" />
                            @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex gap-3 pt-3 mt-2">
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                            {{ $editing ? 'ذخیره تغییرات' : 'ذخیره برچسب' }}
                        </button>
                        <button type="button" wire:click="$set('modalOpen', false)"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            لغو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- مودال تایید حذف --}}
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md p-5 bg-white border shadow-2xl rounded-2xl border-slate-200/50 animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">
                            <x-icon name="trash" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">حذف برچسب</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این برچسب مطمئن هستید؟ ارتباط آن با مقالات نیز حذف خواهد شد.</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteTag" wire:loading.attr="disabled"
                            class="flex-1 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/25 transition hover:bg-rose-600 disabled:opacity-50">
                        بله، حذف شود
                    </button>
                    <button wire:click="$set('confirmingDelete', false)"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        لغو
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
