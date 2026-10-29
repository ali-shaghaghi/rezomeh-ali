<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">رزومه من</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">مدیریت مرکزی نمایش آیتم‌ها در رزومه و سایت اصلی</p>
        </div>
    </div>

    {{-- نوتیفیکیشن موفقیت --}}
    @if($successToast)
        <div class="px-4 py-3 mb-6 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            {{ $successMessage }}
        </div>
    @endif

    {{-- کارت‌های آمار (شمارنده انیمیشنی — الگوی داشبورد) --}}
    <div class="grid gap-4 mb-8 sm:grid-cols-2 xl:grid-cols-5">
        <x-admin-stat-card
            title="نمونه کارهای منتشرشده"
            :count="$stats['published_projects']"
            :suffix="' / ' . $stats['projects']"
            icon="products"
            color="primary"
            :trend="0"
            :delay="0"
            class="stagger-1"
        />
        <x-admin-stat-card
            title="مهارت‌های فعال"
            :count="$stats['active_skills']"
            :suffix="' / ' . $stats['skills']"
            icon="star"
            color="violet"
            :trend="0"
            :delay="100"
            class="stagger-2"
        />
        <x-admin-stat-card
            title="تحصیلات"
            :count="$stats['educations']"
            icon="shield"
            color="cyan"
            :trend="0"
            :delay="200"
            class="stagger-3"
        />
        <x-admin-stat-card
            title="تجربیات کاری"
            :count="$stats['experiences']"
            icon="users"
            color="amber"
            :trend="0"
            :delay="300"
            class="stagger-4"
        />
        <x-admin-stat-card
            title="زبان‌ها"
            :count="$stats['languages']"
            icon="mail"
            color="emerald"
            :trend="0"
            :delay="400"
            class="stagger-5"
        />
    </div>

    {{-- کارت پروفایل رزومه (اطلاعات شخصی — مرجع قالب فرانت‌اند) --}}
    <div class="mb-8 chart-card animate-slide-up">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                {{-- تصویر شخص / حرف اول نام --}}
                @if($profile->avatar)
                    @php
                        // هم مسیرهای قدیمی storage و هم مسیرهای گالری (gallery/{hash}) و آدرس مطلق پشتیبانی می‌شود
                        $avatarUrl = (string) featured_image_url($profile->avatar);
                        $avatarDisk = \Illuminate\Support\Facades\Storage::disk('public');
                        $avatarIsLegacy = ! str_starts_with(ltrim($profile->avatar, '/'), 'gallery/') && ! preg_match('#^https?://#i', $profile->avatar);
                        if ($avatarIsLegacy && $avatarDisk->exists($profile->avatar)) {
                            $avatarUrl .= '?v=' . $avatarDisk->lastModified($profile->avatar);
                        }
                    @endphp
                    <img src="{{ $avatarUrl }}" alt="{{ $profile->name }}"
                         class="object-cover w-16 h-16 border-2 border-primary-100 rounded-2xl dark:border-primary-900/40" />
                @else
                    <div class="flex items-center justify-center w-16 h-16 text-2xl font-black text-white rounded-2xl bg-gradient-to-br from-primary-500 via-primary-600 to-violet-600">
                        {{ mb_substr($profile->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h3 class="text-lg font-bold">{{ $profile->name }}</h3>
                    <p class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ $profile->job_title }}</p>
                    {{-- عنوان‌های تایپ‌رایتر --}}
                    @if($profile->typewriter_texts)
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach(array_slice($profile->typewriter_texts, 0, 3) as $text)
                                <span class="px-2 py-0.5 text-xs rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $text }}</span>
                            @endforeach
                            @if(count($profile->typewriter_texts) > 3)
                                <span class="px-2 py-0.5 text-xs font-medium rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">+{{ count($profile->typewriter_texts) - 3 }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <button wire:click="editProfile"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition border rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-surface-800">
                <x-icon name="edit" size="4" />
                ویرایش پروفایل
            </button>
        </div>

        {{-- اطلاعات تماس و شبکه‌های اجتماعی --}}
        <div class="grid gap-3 pt-4 mt-4 border-t border-slate-100 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-700">
            <div class="text-sm">
                <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">موقعیت مکانی</p>
                <p class="font-medium">{{ $profile->location ?: '—' }}</p>
            </div>
            <div class="text-sm">
                <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">ایمیل</p>
                <p class="font-medium break-all">{{ $profile->email ?: '—' }}</p>
            </div>
            <div class="text-sm">
                <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">گیت‌هاب</p>
                @if($profile->github_url)
                    <a href="{{ $profile->github_url }}" target="_blank" rel="noopener noreferrer" class="font-medium break-all text-primary-600 dark:text-primary-400 hover:underline" dir="ltr">{{ $profile->github_url }}</a>
                @else
                    <p class="font-medium">—</p>
                @endif
            </div>
            <div class="text-sm">
                <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">لینکدین</p>
                @if($profile->linkedin_url)
                    <a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="font-medium break-all text-primary-600 dark:text-primary-400 hover:underline" dir="ltr">{{ $profile->linkedin_url }}</a>
                @else
                    <p class="font-medium">—</p>
                @endif
            </div>
        </div>

        {{-- شبکه‌های اجتماعی (آیکون اختصاصی هر کدام) --}}
        @php
            $socialLinks = profile_social_links($profile);
        @endphp
        @if(count($socialLinks))
            <div class="flex flex-wrap gap-2 pt-4 mt-4 border-t border-slate-100 dark:border-slate-700">
                @foreach($socialLinks as $link)
                    <a href="{{ $link['url'] }}"
                       @if(!in_array($link['key'], ['phone', 'email'])) target="_blank" rel="noopener noreferrer" @endif
                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-50 hover:text-primary-600 dark:bg-slate-700/60 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300"
                       title="{{ $link['label'] }}">
                        <x-social :name="$link['icon']" class="w-4 h-4" />
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- توضیحات (بایو) --}}
        @if($profile->bio)
            <p class="pt-4 mt-4 text-sm leading-7 border-t text-slate-600 dark:text-slate-300 border-slate-100 dark:border-slate-700">{{ $profile->bio }}</p>
        @endif

        {{-- فایل رزومه --}}
        @if($profile->resume_file)
            <p class="pt-3 text-xs text-slate-500 dark:text-slate-400">
                فایل رزومه:
                <a href="{{ asset('storage/' . $profile->resume_file) }}" target="_blank" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">مشاهده PDF</a>
            </p>
        @endif
    </div>

    {{-- مودال ویرایش پروفایل (پس‌زمینه بلور + انیمیشن — الگوی Roles) --}}
    @if($profileEditing)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 bg-black/50 backdrop-blur-sm ">
            <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="users" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">ویرایش پروفایل رزومه</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">اطلاعاتی که در سایت عمومی نمایش داده می‌شود</p>
                        </div>
                    </div>
                    <button wire:click="$set('profileEditing', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                <form wire:submit.prevent="saveProfile" enctype="multipart/form-data" class="space-y-6">
                    {{-- ===== بخش فارسی (اصلی) ===== --}}
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-700">
                        <span class="text-base">🇮🇷</span>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">نسخه فارسی (اصلی)</h4>
                    </div>

                    {{-- نام و عنوان شغلی --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام کامل <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="profile_name" required
                                   class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                   placeholder="مثال: علی شقاقی" />
                            @error('profile_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">عنوان شغلی <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="profile_job_title" required
                                   class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                   placeholder="مثال: توسعه‌دهنده ارشد فرانت‌اند" />
                            @error('profile_job_title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- موقعیت و ایمیل --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">موقعیت مکانی</label>
                            <input type="text" wire:model.blur="profile_location"
                                   class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                   placeholder="مثال: تهران، ایران" />
                            @error('profile_location') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">ایمیل</label>
                            <input type="email" wire:model.blur="profile_email" dir="ltr"
                                   class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                   placeholder="hello@example.com" />
                            @error('profile_email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- سوشال مدیا: تلگرام، واتساپ، اینستاگرام، روبیکا، بله، ایتا، تلفن همراه، ایمیل --}}
                    @include('resume::livewire.admin.partials.social-fields')

                    {{-- گیت‌هاب و لینکدین --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="flex items-center gap-1.5 mb-1 text-xs font-medium text-slate-700 dark:text-slate-300"><x-social name="github" class="w-3.5 h-3.5" /> گیت‌هاب</label>
                            <input type="url" wire:model.blur="profile_github_url" dir="ltr"
                                   class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                   placeholder="https://github.com/username" />
                            @error('profile_github_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="flex items-center gap-1.5 mb-1 text-xs font-medium text-slate-700 dark:text-slate-300"><x-social name="linkedin" class="w-3.5 h-3.5" /> لینکدین</label>
                            <input type="url" wire:model.blur="profile_linkedin_url" dir="ltr"
                                   class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                   placeholder="https://linkedin.com/in/username" />
                            @error('profile_linkedin_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- عنوان‌های تایپ‌رایتر (هر خط یک عنوان — تغییر رییل در فرانت‌اند) --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">عنوان‌های تایپ‌رایتر (هر خط یک عنوان شغلی)</label>
                        <textarea wire:model.blur="profile_typewriter_texts" rows="3"
                                  class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                  placeholder="توسعه‌دهنده ارشد فرانت‌اند&#10;طراح رابط کاربری مدرن&#10;متخصص React و Vue"></textarea>
                        @error('profile_typewriter_texts') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- توضیحات (بایو) --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">توضیحات (بایو)</label>
                        <textarea wire:model.blur="profile_bio" rows="3"
                                  class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                  placeholder="معرفی کوتاه خودتان..."></textarea>
                        @error('profile_bio') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- تصویر شخص و فایل رزومه --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div x-data="{
                                pickerOpen: false,
                                pick: null,
                                assets: {{ $avatarPickerAssetsJson }},
                                confirmPick() {
                                    if (! this.pick) return;
                                    this.pickerOpen = false;
                                    $wire.call('pickAvatarImage', this.pick.src);
                                }
                             }">
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">تصویر شخص</label>
                            @php
                                $avatarPreview = $profile_avatar && method_exists($profile_avatar, 'temporaryUrl')
                                    ? $profile_avatar->temporaryUrl()
                                    : ($profileAvatarSrc
                                        ? featured_image_url($profileAvatarSrc)
                                        : ($existingAvatar ? featured_image_url($existingAvatar) : null));
                            @endphp
                            @if($avatarPreview)
                                <div class="relative mb-2 group w-fit">
                                    <img src="{{ $avatarPreview }}" alt="تصویر شخص" class="object-cover w-16 h-16 rounded-xl border border-slate-200 dark:border-slate-600" />
                                    <button type="button" wire:click="removeAvatar" wire:loading.attr="disabled"
                                            class="absolute p-1 text-white transition rounded-lg opacity-0 top-1 left-1 bg-black/50 group-hover:opacity-100 hover:bg-rose-600">
                                        <x-icon name="close" size="3" />
                                    </button>
                                </div>
                            @endif
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="pick = null; pickerOpen = true"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-primary-500 px-3 py-2 text-xs font-bold text-white shadow-md shadow-primary-500/25 transition hover:bg-primary-600">
                                    🖼️ انتخاب از گالری
                                </button>
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                                    ⇪ آپلود فایل
                                    <input type="file" wire:model="profile_avatar" accept="image/*" class="hidden">
                                </label>
                                <span wire:loading wire:target="profile_avatar" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary-600">
                                    <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    در حال آپلود…
                                </span>
                            </div>
                            @error('profile_avatar') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @error('profileAvatarSrc') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                            @include('resume::livewire.admin.partials.gallery-picker-modal')
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">فایل رزومه (PDF)</label>
                            @if($existingResumeFile)
                                <div class="flex items-center gap-2 mb-2">
                                    <a href="{{ asset('storage/' . $existingResumeFile) }}" target="_blank"
                                       class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">{{ basename($existingResumeFile) }}</a>
                                    <button type="button" wire:click="removeResumeFile" wire:loading.attr="disabled"
                                            class="p-1 transition rounded-lg text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-900/30">
                                        <x-icon name="trash" size="3" />
                                    </button>
                                </div>
                            @endif
                            <input type="file" wire:model="profile_resume_file" accept=".pdf"
                                   class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                            @error('profile_resume_file') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- ===== بخش انگلیسی (اختیاری — در سایت عمومی نمایش داده می‌شود اگر کاربر زبان انگلیسی را انتخاب کند) ===== --}}
                    <div class="p-4 space-y-4 border rounded-2xl border-slate-200/70 bg-slate-50/50 dark:border-slate-700/60 dark:bg-slate-800/30">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🇬🇧</span>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">English version</h4>
                            </div>
                            <span class="px-2 py-0.5 text-[11px] font-medium rounded-lg bg-slate-200/70 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300">اختیاری — در صورت خالی بودن، نسخه فارسی نمایش داده می‌شود</span>
                        </div>

                        {{-- نام و عنوان شغلی انگلیسی --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">Full name (English)</label>
                                <input type="text" wire:model.blur="profile_name_en" dir="ltr"
                                       class="w-full px-3 py-2 text-sm transition bg-white border rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                       placeholder="e.g. Ali Shaghaghi" />
                                @error('profile_name_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">Job title (English)</label>
                                <input type="text" wire:model.blur="profile_job_title_en" dir="ltr"
                                       class="w-full px-3 py-2 text-sm transition bg-white border rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                       placeholder="e.g. Senior Front-end Developer" />
                                @error('profile_job_title_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- موقعیت انگلیسی --}}
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">Location (English)</label>
                            <input type="text" wire:model.blur="profile_location_en" dir="ltr"
                                   class="w-full px-3 py-2 text-sm transition bg-white border rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                   placeholder="e.g. Tehran, Iran" />
                            @error('profile_location_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- توضیحات انگلیسی --}}
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">Bio (English)</label>
                            <textarea wire:model.blur="profile_bio_en" dir="ltr" rows="3"
                                      class="w-full px-3 py-2 text-sm transition bg-white border resize-none rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                      placeholder="Short English description about yourself..."></textarea>
                            @error('profile_bio_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- عنوان‌های تایپ‌رایتر انگلیسی --}}
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">Typewriter job titles (English) — هر خط یک عنوان</label>
                            <textarea wire:model.blur="profile_typewriter_texts_en" dir="ltr" rows="3"
                                      class="w-full px-3 py-2 text-sm transition bg-white border resize-none rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                      placeholder="Web Developer&#10;Laravel Specialist"></textarea>
                            @error('profile_typewriter_texts_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex gap-3 pt-2">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile"
                                class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                            ذخیره پروفایل
                        </button>
                        <button type="button" wire:click="$set('profileEditing', false)"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            لغو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- تب‌ها --}}
    <div class="flex flex-wrap gap-2 mb-6 ">
        @php
            $tabs = [
                'projects' => ['label' => 'نمونه کارها', 'icon' => 'products', 'count' => $stats['projects']],
                'skills' => ['label' => 'مهارت‌ها', 'icon' => 'star', 'count' => $stats['skills']],
                'educations' => ['label' => 'تحصیلات', 'icon' => 'shield', 'count' => $stats['educations']],
                'experiences' => ['label' => 'تجربیات', 'icon' => 'users', 'count' => $stats['experiences']],
                'languages' => ['label' => 'زبان‌ها', 'icon' => 'mail', 'count' => $stats['languages']],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
            <button wire:click="setTab('{{ $key }}')"
                    class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition {{ $activeTab === $key
                        ? 'bg-primary-500 text-white shadow-md shadow-primary-500/25'
                        : 'border border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <x-icon name="{{ $tab['icon'] }}" size="4" />
                {{ $tab['label'] }}
                <span class="px-1.5 py-0.5 text-xs rounded-lg {{ $activeTab === $key ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-700' }}">{{ $tab['count'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- ===== تب پروژه‌ها ===== --}}
    @if($activeTab === 'projects')
        <div class="space-y-3">
            @forelse($projects as $project)
                <div class="flex flex-wrap items-center gap-4 p-4 mb-3 chart-card">
                    {{-- عنوان و دسته --}}
                    <div class="flex items-center flex-1 gap-3 min-w-[200px]">
                        <span class="flex items-center justify-center h-10 w-10 shrink-0 rounded-xl {{ $project->status === 'published' ? 'bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400' : 'bg-slate-100 text-slate-400 dark:bg-slate-700' }}">
                            <x-icon name="products" size="5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-bold truncate">{{ $project->title }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $project->category->name ?? 'بدون دسته' }} · ترتیب {{ $project->order }}</p>
                        </div>
                    </div>

                    {{-- بج‌های وضعیت --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $project->status === 'published' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                            {{ $project->status === 'published' ? 'در رزومه' : 'خارج از رزومه' }}
                        </span>
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $project->featured ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-500' }}">
                            {{ $project->featured ? 'برگزیده' : 'عادی' }}
                        </span>
                    </div>

                    {{-- اکشن‌ها --}}
                    <div class="flex items-center gap-2">
                        <button wire:click="moveProject({{ $project->id }}, 'up')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700" title="انتقال به بالا">
                            <x-icon name="chevronDown" size="4" class="rotate-180" />
                        </button>
                        <button wire:click="moveProject({{ $project->id }}, 'down')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700" title="انتقال به پایین">
                            <x-icon name="chevronDown" size="4" />
                        </button>
                        <button wire:click="toggleProjectStatus({{ $project->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $project->status === 'published' ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400' : 'border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300' }}">
                            {{ $project->status === 'published' ? 'حذف از رزومه' : 'افزودن به رزومه' }}
                        </button>
                        <button wire:click="toggleProjectFeatured({{ $project->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $project->featured ? 'border-amber-200 text-amber-600 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-400' : 'border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300' }}">
                            {{ $project->featured ? 'لغو برگزیده' : 'برگزیده' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center chart-card">
                    <p class="text-slate-500 dark:text-slate-400">نمونه کاری ثبت نشده — از <a href="{{ route('admin.resume.portfolio.index') }}" class="text-primary-500 hover:underline">بخش نمونه کار</a> اضافه کنید</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- ===== تب مهارت‌ها ===== --}}
    @if($activeTab === 'skills')
        <div class="space-y-3">
            @forelse($skills as $skill)
                <div class="flex flex-wrap items-center gap-4 p-4 mb-3 chart-card">
                    <div class="flex items-center flex-1 gap-3 min-w-[200px]">
                        <span class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="star" size="5" />
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold">{{ $skill->name }}</h3>
                                <span class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ $skill->proficiency }}٪</span>
                            </div>
                            {{-- نوار پیشرفت --}}
                            <div class="w-full h-1.5 mt-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-gradient-to-l from-primary-500 to-violet-600" style="width: {{ $skill->proficiency }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $skill->category_label }}</span>
                        <button wire:click="moveSkill({{ $skill->id }}, 'up')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <x-icon name="chevronDown" size="4" class="rotate-180" />
                        </button>
                        <button wire:click="moveSkill({{ $skill->id }}, 'down')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <x-icon name="chevronDown" size="4" />
                        </button>
                        <button wire:click="toggleSkillActive({{ $skill->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $skill->is_active ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400' : 'border-slate-200 text-slate-600' }}">
                            {{ $skill->is_active ? 'فعال' : 'غیرفعال' }}
                        </button>
                        <button wire:click="toggleSkillResume({{ $skill->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $skill->show_in_resume ? 'border-primary-200 bg-primary-50 text-primary-600 dark:border-primary-800 dark:bg-primary-900/20 dark:text-primary-400' : 'border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-600' }}">
                            {{ $skill->show_in_resume ? 'در رزومه ✓' : 'افزودن به رزومه' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center chart-card">
                    <p class="text-slate-500 dark:text-slate-400">مهارتی ثبت نشده — از <a href="{{ route('admin.resume.skills.index') }}" class="text-primary-500 hover:underline">بخش مهارت‌ها</a> اضافه کنید</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- ===== تب تحصیلات ===== --}}
    @if($activeTab === 'educations')
        <div class="space-y-3">
            @forelse($educations as $education)
                <div class="flex flex-wrap items-center gap-4 p-4 mb-3 chart-card">
                    <div class="flex items-center flex-1 gap-3 min-w-[200px]">
                        <span class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="shield" size="5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-bold">{{ $education->institution }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $education->degree }} · {{ $education->field_of_study }} · ترتیب {{ $education->order }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click="moveEducation({{ $education->id }}, 'up')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <x-icon name="chevronDown" size="4" class="rotate-180" />
                        </button>
                        <button wire:click="moveEducation({{ $education->id }}, 'down')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <x-icon name="chevronDown" size="4" />
                        </button>
                        <button wire:click="toggleEducationActive({{ $education->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $education->is_active ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400' : 'border-slate-200 text-slate-600' }}">
                            {{ $education->is_active ? 'فعال' : 'غیرفعال' }}
                        </button>
                        <button wire:click="toggleEducationResume({{ $education->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $education->show_in_resume ? 'border-primary-200 bg-primary-50 text-primary-600 dark:border-primary-800 dark:bg-primary-900/20 dark:text-primary-400' : 'border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-600' }}">
                            {{ $education->show_in_resume ? 'در رزومه ✓' : 'افزودن به رزومه' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center chart-card">
                    <p class="text-slate-500 dark:text-slate-400">مقطع تحصیلی ثبت نشده — از <a href="{{ route('admin.resume.education.index') }}" class="text-primary-500 hover:underline">بخش تحصیلات</a> اضافه کنید</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- ===== تب تجربیات ===== --}}
    @if($activeTab === 'experiences')
        <div class="space-y-3">
            @forelse($experiences as $experience)
                <div class="flex flex-wrap items-center gap-4 p-4 mb-3 chart-card">
                    <div class="flex items-center flex-1 gap-3 min-w-[200px]">
                        <span class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="users" size="5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-bold">{{ $experience->position }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $experience->company }} · {{ $experience->employment_type_label }} · ترتیب {{ $experience->order }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click="moveExperience({{ $experience->id }}, 'up')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <x-icon name="chevronDown" size="4" class="rotate-180" />
                        </button>
                        <button wire:click="moveExperience({{ $experience->id }}, 'down')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <x-icon name="chevronDown" size="4" />
                        </button>
                        <button wire:click="toggleExperienceActive({{ $experience->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $experience->is_active ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400' : 'border-slate-200 text-slate-600' }}">
                            {{ $experience->is_active ? 'فعال' : 'غیرفعال' }}
                        </button>
                        <button wire:click="toggleExperienceResume({{ $experience->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $experience->show_in_resume ? 'border-primary-200 bg-primary-50 text-primary-600 dark:border-primary-800 dark:bg-primary-900/20 dark:text-primary-400' : 'border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-600' }}">
                            {{ $experience->show_in_resume ? 'در رزومه ✓' : 'افزودن به رزومه' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center chart-card">
                    <p class="text-slate-500 dark:text-slate-400">سابقه کاری ثبت نشده — از <a href="{{ route('admin.resume.experience.index') }}" class="text-primary-500 hover:underline">بخش تجربیات</a> اضافه کنید</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- ===== تب زبان‌ها ===== --}}
    @if($activeTab === 'languages')
        <div class="space-y-3">
            @forelse($languages as $language)
                <div class="flex flex-wrap items-center gap-4 p-4 mb-3 chart-card">
                    <div class="flex items-center flex-1 gap-3 min-w-[200px]">
                        <span class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="mail" size="5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-bold">{{ $language->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $language->level_label }} · ترتیب {{ $language->order }}</p>
                        </div>
                    </div>

                    {{-- نوار تسلط --}}
                    <div class="w-32">
                        <div class="flex items-center justify-between mb-1 text-xs">
                            <span class="text-slate-500 dark:text-slate-400">تسلط</span>
                            <span class="font-bold text-primary-600 dark:text-primary-400">{{ $language->proficiency }}٪</span>
                        </div>
                        <div class="w-full h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                            <div class="h-full rounded-full bg-gradient-to-l from-primary-500 to-violet-600" style="width: {{ $language->proficiency }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click="moveLanguage({{ $language->id }}, 'up')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <x-icon name="chevronDown" size="4" class="rotate-180" />
                        </button>
                        <button wire:click="moveLanguage({{ $language->id }}, 'down')" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <x-icon name="chevronDown" size="4" />
                        </button>
                        <button wire:click="toggleLanguageActive({{ $language->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $language->is_active ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400' : 'border-slate-200 text-slate-600' }}">
                            {{ $language->is_active ? 'فعال' : 'غیرفعال' }}
                        </button>
                        <button wire:click="toggleLanguageResume({{ $language->id }})"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $language->show_in_resume ? 'border-primary-200 bg-primary-50 text-primary-600 dark:border-primary-800 dark:bg-primary-900/20 dark:text-primary-400' : 'border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-600' }}">
                            {{ $language->show_in_resume ? 'در رزومه ✓' : 'افزودن به رزومه' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center chart-card">
                    <p class="text-slate-500 dark:text-slate-400">زبانی ثبت نشده — از <a href="{{ route('admin.resume.languages.index') }}" class="text-primary-500 hover:underline">بخش زبان‌ها</a> اضافه کنید</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
