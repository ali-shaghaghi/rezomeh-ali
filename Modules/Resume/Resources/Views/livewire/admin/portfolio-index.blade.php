<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">نمونه‌کارها</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $projects->total() }} پروژه ثبت‌شده</p>
        </div>
        @can('create', \Modules\Resume\Models\Project::class)
            <a href="{{ route('admin.resume.portfolio.create') }}"
               class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                افزودن پروژه جدید
            </a>
        @endcan
    </div>

    {{-- جستجو و فیلتر ریاکتیو --}}
    <div class="mb-6 animate-slide-up chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی عنوان، اسلاگ یا توضیحات..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-10 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            <select wire:model.live="status"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="published">منتشر شده</option>
                <option value="draft">پیش‌نویس</option>
                <option value="archived">آرشیو</option>
            </select>
            <select wire:model.live="categoryId"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه دسته‌بندی‌ها</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="featured"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه پروژه‌ها</option>
                <option value="1">فقط ویژه</option>
                <option value="0">غیر ویژه</option>
            </select>
            @if($search || $status || $categoryId || $featured !== '')
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

    {{-- ایندیکیتور لودینگ — فقط هنگام فیلتر/جستجو، شفاف و بدون جابجایی صفحه --}}
    <div wire:loading.delay.200ms class="fixed z-40 items-center hidden gap-2 px-4 py-2 text-xs font-medium text-white rounded-full shadow-lg bottom-6 left-6 bg-primary-500/90 backdrop-blur-sm" wire:target="search, status, categoryId, featured, resetFilters, deleteProject, toggleStatus, toggleFeatured, move">
        <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        در حال بارگذاری...
    </div>

    {{-- گرید پروژه‌ها — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="grid gap-5 animate-slide-up sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="pointer-events-none opacity-60" wire:target="deleteProject, toggleStatus, toggleFeatured, move">
        @forelse($projects as $project)
            <article class="flex flex-col p-0 mt-2 mb-2 ml-2 mr-2 overflow-hidden chart-card group">
                {{-- پیش‌نمایش تصویر / گرادیان --}}
                <div class="relative flex items-center justify-center h-40 transition bg-gradient-to-bl from-slate-100 to-slate-50 group-hover:from-primary-50 group-hover:to-violet-50 dark:from-slate-800 dark:to-slate-900 dark:group-hover:from-primary-950/50">
                    @if($project->localizedCover())
                        <img src="{{ featured_image_url($project->localizedCover()) }}" alt="{{ $project->title }}"
                             class="object-cover w-full h-full" />
                    @else
                        <span class="text-primary-400"><x-icon name="products" size="10" /></span>
                    @endif

                    {{-- بج وضعیت --}}
                    <span class="absolute top-3 left-3 px-2.5 py-1 text-xs font-medium rounded-full backdrop-blur-sm {{ $project->status === 'published' ? 'bg-emerald-100/90 text-emerald-700 dark:bg-emerald-900/70 dark:text-emerald-300' : ($project->status === 'draft' ? 'bg-amber-100/90 text-amber-700 dark:bg-amber-900/70 dark:text-amber-300' : 'bg-rose-100/90 text-rose-700 dark:bg-rose-900/70 dark:text-rose-300') }}">
                        {{ $project->status_label }}
                    </span>

                    {{-- بج ویژه --}}
                    @if($project->featured)
                        <span class="absolute flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full top-3 right-3 bg-primary-100/90 text-primary-700 backdrop-blur-sm dark:bg-primary-900/70 dark:text-primary-300">
                            <x-icon name="star" size="3" />
                            ویژه
                        </span>
                    @endif
                </div>

                <div class="flex flex-col flex-1 p-4">
                    <h3 class="font-bold">{{ $project->title }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $project->category->name ?? 'بدون دسته‌بندی' }}</p>

                    {{-- توضیح کوتاه --}}
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        {{ $project->short_description ?: \Illuminate\Support\Str::limit($project->description, 70) }}
                    </p>

                    {{-- تکنولوژی‌ها --}}
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($project->technologies->take(3) as $tech)
                            <span class="px-2 py-1 text-xs rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $tech->name }}</span>
                        @endforeach
                        @if($project->technologies->count() > 3)
                            <span class="px-2 py-1 text-xs font-medium rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">
                                +{{ $project->technologies->count() - 3 }}
                            </span>
                        @endif
                    </div>

                    {{-- تاریخ و لینک دمو --}}
                    <div class="flex items-center justify-between mt-3 text-xs text-slate-400 dark:text-slate-500">
                        <span>{{ $project->project_date?->format('Y/m/d') ?: $project->created_at->format('Y/m/d') }}</span>
                        @if($project->demo_url)
                            <button wire:click="openDemo({{ $project->id }})"
                                    class="flex items-center gap-1 transition text-primary-500 hover:text-primary-600 dark:text-primary-400">
                                <x-icon name="eye" size="3" />
                                نمایش دمو
                            </button>
                        @endif
                    </div>

                    {{-- اکشن‌های ریاکتیو: انتشار و ویژه --}}
                    <div class="flex gap-2 mt-3">
                        @can('update', $project)
                            <button wire:click="toggleStatus({{ $project->id }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 rounded-lg border py-2 text-xs font-medium transition hover:border-primary-300 dark:border-slate-600 {{ $project->status === 'published' ? 'text-emerald-600' : 'text-slate-600 dark:text-slate-300' }}">
                                {{ $project->status === 'published' ? 'لغو انتشار' : 'انتشار' }}
                            </button>
                            <button wire:click="toggleFeatured({{ $project->id }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 rounded-lg border py-2 text-xs font-medium transition hover:border-primary-300 dark:border-slate-600 {{ $project->featured ? 'text-amber-500' : 'text-slate-600 dark:text-slate-300' }}">
                                {{ $project->featured ? 'لغو ویژه' : 'ویژه کردن' }}
                            </button>
                        @endcan
                    </div>

                    {{-- دکمه‌های اصلی --}}
                    <div class="flex gap-2 mt-3">
                        <a href="{{ route('admin.resume.portfolio.show', $project) }}"
                           class="flex-1 py-2 text-xs font-medium text-center transition border rounded-lg border-slate-200 hover:border-primary-300 dark:border-slate-600">
                            مشاهده
                        </a>
                        @can('update', $project)
                            <a href="{{ route('admin.resume.portfolio.edit', $project) }}"
                               class="flex-1 py-2 text-xs font-medium text-center transition border rounded-lg border-slate-200 hover:border-primary-300 dark:border-slate-600">
                                ویرایش
                            </a>
                        @endcan
                        @can('delete', $project)
                            <button wire:click="confirmDelete({{ $project->id }})"
                                    class="flex-1 py-2 text-xs font-medium transition border rounded-lg border-slate-200 text-rose-500 hover:bg-rose-50 dark:border-slate-600 dark:text-rose-400 dark:hover:bg-rose-900/30">
                                حذف
                            </button>
                        @endcan
                    </div>
                </div>
            </article>
        @empty
            {{-- خالی بودن استیت --}}
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="products" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'پروژه‌ای مطابق جستجوی شما یافت نشد' : 'هنوز پروژه‌ای ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید یا فیلترها را حذف کنید' : 'اولین نمونه‌کار خود را اضافه کنید' }}</p>
                @can('create', \Modules\Resume\Models\Project::class)
                    <a href="{{ route('admin.resume.portfolio.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 mt-4 text-sm font-semibold text-white transition shadow-md rounded-xl bg-primary-500 shadow-primary-500/30 hover:brightness-110">
                        <x-icon name="plus" size="3" />
                        افزودن پروژه
                    </a>
                @endcan
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($projects->hasPages())
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    @endif

    {{-- مودال تایید حذف (پس‌زمینه بلور + انیمیشن) --}}
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" wire:close="confirmDelete">
            <div class="w-full max-w-md p-5 bg-white border shadow-2xl rounded-2xl border-slate-200/50 animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">
                            <x-icon name="trash" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">حذف پروژه</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این پروژه مطمئن هستید؟ تمام تصاویر و روابط آن نیز حذف خواهند شد.</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteProject" wire:loading.attr="disabled"
                            class="flex-1 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/25 transition hover:bg-rose-600">
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

    {{-- مودال نمایش دمو --}}
    @if($showDemo)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" wire:close="showDemo">
            <div class="w-full max-w-5xl overflow-hidden bg-white border shadow-2xl rounded-2xl border-slate-200/50 animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="eye" size="4" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold">دموی پروژه: {{ $demoTitle }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $demoUrl }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ $demoUrl }}" target="_blank" rel="noopener noreferrer"
                           class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            بازکردن در تب جدید
                        </a>
                        <button wire:click="$set('showDemo', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                            <x-icon name="close" />
                        </button>
                    </div>
                </div>
                <iframe src="{{ $demoUrl }}" class="w-full h-[70vh]" frameborder="0" title="دموی {{ $demoTitle }}"></iframe>
            </div>
        </div>
    @endif

    @once
        @push('scripts')
            <script>
                // انیمیشن ورود کارت‌ها با stagger
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.chart-card').forEach((card, i) => {
                        setTimeout(() => card.classList.add('show'), i * 60);
                    });
                });
            </script>
        @endpush
    @endonce
</div>
