<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold sm:text-3xl">{{ $project->title }}</h2>
                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $project->status === 'published' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : ($project->status === 'draft' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300') }}">
                    {{ $project->status_label }}
                </span>
                @if($project->featured)
                    <span class="flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                        <x-icon name="star" size="3" />
                        ویژه
                    </span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" dir="ltr">slug: {{ $project->slug }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($demoLink)
                <button wire:click="openDemo"
                        class="flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all hover:shadow-xl">
                    <x-icon name="eye" size="4" />
                    نمایش دمو
                </button>
            @endif
            @can('update', $project)
                <a href="{{ route('admin.resume.portfolio.edit', $project) }}"
                   class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-surface-800">
                    <x-icon name="edit" size="4" />
                    ویرایش
                </a>
            @endcan
            <a href="{{ route('admin.resume.portfolio.index') }}"
               class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-surface-800">
                بازگشت
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- ستون اصلی --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- تصویر کاور --}}
            @if($project->localizedCover())
                <div class="overflow-hidden border rounded-2xl chart-card border-slate-200 dark:border-slate-700">
                    <img src="{{ featured_image_url($project->localizedCover()) }}" alt="{{ $project->title }}" class="object-cover w-full h-72">
                </div>
            @endif

            {{-- توضیح کوتاه + کامل --}}
            <div class="p-6 chart-card">
                @if($project->short_description)
                    <p class="pb-4 mb-4 text-lg leading-relaxed border-b text-slate-600 dark:text-slate-300 border-slate-100 dark:border-slate-700">{{ $project->short_description }}</p>
                @endif
                <h3 class="mb-3 text-lg font-bold">توضیحات پروژه</h3>
                <p class="leading-8 whitespace-pre-line text-slate-600 dark:text-slate-300">{{ $project->description }}</p>
            </div>

            {{-- تکنولوژی‌ها و مهارت‌ها --}}
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">تکنولوژی‌ها</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($project->technologies as $tech)
                            <span class="px-3 py-1.5 text-sm rounded-lg text-slate-700 bg-slate-100 dark:bg-slate-700 dark:text-slate-300">{{ $tech->name }}</span>
                        @empty
                            <span class="text-sm text-slate-400">ثبت نشده</span>
                        @endforelse
                    </div>
                </div>
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">مهارت‌های مرتبط</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($project->skills as $skill)
                            <span class="px-3 py-1.5 text-sm rounded-lg bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                                {{ $skill->name }} · {{ $skill->proficiency }}٪
                            </span>
                        @empty
                            <span class="text-sm text-slate-400">ثبت نشده</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ستون کناری: اطلاعات پروژه --}}
        <div class="space-y-6">
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">اطلاعات پروژه</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">دسته‌بندی</dt>
                        <dd class="font-medium">{{ $project->category->name ?? 'بدون دسته' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">مشتری</dt>
                        <dd class="font-medium">{{ $project->client_name ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">تاریخ پروژه</dt>
                        <dd class="font-medium" dir="ltr">{{ $project->project_date?->format('Y/m/d') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">ترتیب نمایش</dt>
                        <dd class="font-medium" dir="ltr">{{ $project->order }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">تاریخ ایجاد</dt>
                        <dd class="font-medium" dir="ltr">{{ $project->created_at->format('Y/m/d') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- لینک‌ها --}}
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">لینک‌ها</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">GitHub</p>
                        @if($project->github_url)
                            <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer" class="break-all text-primary-600 dark:text-primary-400 hover:underline" dir="ltr">{{ $project->github_url }}</a>
                        @else
                            <p class="text-slate-400">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">دمو ({{ ($project->demo_type ?? 'link') === 'subdomain' ? 'زیردامنه' : (($project->demo_type ?? 'link') === 'external_url' ? 'آدرس خارجی' : 'لینک') }})</p>
                        @if($demoLink)
                            <a href="{{ $demoLink }}" target="_blank" rel="noopener noreferrer" class="break-all text-primary-600 dark:text-primary-400 hover:underline" dir="ltr">{{ $demoLink }}</a>
                        @else
                            <p class="text-slate-400">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">ویدئو</p>
                        @if($project->video_url)
                            <a href="{{ $project->video_url }}" target="_blank" rel="noopener noreferrer" class="break-all text-primary-600 dark:text-primary-400 hover:underline" dir="ltr">{{ $project->video_url }}</a>
                        @else
                            <p class="text-slate-400">—</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            @if($project->seo_title || $project->seo_description)
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">SEO</h3>
                    <div class="space-y-2 text-sm">
                        <p class="text-slate-500 dark:text-slate-400">عنوان: <span class="text-slate-700 dark:text-slate-200">{{ $project->seo_title ?: '—' }}</span></p>
                        <p class="text-slate-500 dark:text-slate-400">توضیحات: <span class="text-slate-700 dark:text-slate-200">{{ $project->seo_description ?: '—' }}</span></p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- مودال نمایش دمو (iframe) --}}
    @if($showDemo && $demoLink)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" wire:close="showDemo">
            <div class="w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200/50 bg-white shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center h-8 w-8 rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="eye" size="4" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold">دموی پروژه: {{ $project->title }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $demoLink }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ $demoLink }}" target="_blank" rel="noopener noreferrer"
                           class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            بازکردن در تب جدید
                        </a>
                        <button wire:click="$set('showDemo', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                            <x-icon name="close" />
                        </button>
                    </div>
                </div>
                <iframe src="{{ $demoLink }}" class="w-full h-[70vh]" frameborder="0" title="دموی پروژه {{ $project->title }}"></iframe>
            </div>
        </div>
    @endif
</div>
