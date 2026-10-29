<div class="space-y-5">
    {{-- ===== هدر ===== --}}
    <div class="flex items-center gap-4">
        <span class="flex items-center justify-center w-12 h-12 text-white shrink-0 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </span>
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white">درخواست مشاوره</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">زمان‌های آزاد به‌صورت زنده نمایش داده می‌شوند</p>
        </div>
    </div>

    {{-- ===== نوار مراحل ===== --}}
    <div class="flex items-center justify-between gap-1" wire:key="wizard-steps">
        @foreach (['نوع مشاوره', 'انتخاب زمان', 'اطلاعات', 'تایید'] as $i => $label)
            <div class="flex items-center flex-1">
                <button type="button" wire:click="prevStep" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-black transition {{ $step >= $i + 1 ? 'bg-primary-600 text-white' : 'bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}"
                        {{ $step <= $i + 1 ? 'disabled' : '' }}>{{ fa_digits($i + 1) }}</button>
                <span class="mr-2 hidden text-xs font-bold text-slate-500 sm:inline {{ $step >= $i + 1 ? '!text-primary-600 dark:!text-primary-400' : '' }}">{{ $label }}</span>
                @if ($i < 3)
                    <div class="mx-2 h-0.5 flex-1 rounded-full {{ $step > $i + 1 ? 'bg-primary-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    @if ($errors->any())
        <div class="p-4 border rounded-2xl border-rose-200 bg-rose-50 dark:border-rose-800 dark:bg-rose-950/40">
            @foreach ($errors->all() as $error)
                <p class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ===== مرحله ۱: انتخاب نوع مشاوره ===== --}}
    @if ($step === 1)
        @if ($consultant)
            <div class="relative flex flex-col gap-5 p-4 p-6 mb-4 group glass-card reveal sm:flex-row sm:items-center post-card animate-slide-up">
                <span class="flex items-center justify-center w-12 h-12 text-lg font-black text-white shrink-0 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500">
                    {{ mb_substr($consultant->personName(), 0, 1) }}
                </span>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $consultant->personName() }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $consultant->specialty }}</p>
                    @if ($consultant->bio)
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-400">{{ \Illuminate\Support\Str::limit($consultant->bio, 120) }}</p>
                    @endif
                </div>
                @if ($consultant->rating > 0)
                    <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">★ {{ fa_digits($consultant->rating) }}</span>
                @endif
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-3" wire:key="step-types">
            @forelse ($types as $t)
                <button type="button" wire:click="chooseType({{ $t->id }})" wire:key="type-{{ $t->id }}"
                        class="p-5 text-right transition glass-card-hover group">
                    <span class="flex items-center justify-center w-10 h-10 mb-3 rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400">
                        @if ($t->mode === 'online')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        @elseif ($t->mode === 'phone')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        @endif
                    </span>
                    <h3 class="font-bold text-slate-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">{{ $t->name }}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $t->mode === 'online' ? 'آنلاین' : ($t->mode === 'phone' ? 'تلفنی' : 'حضوری') }} · {{ fa_digits($t->duration_minutes) }} دقیقه</p>
                    @if ($t->description)
                        <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($t->description, 70) }}</p>
                    @endif
                    <p class="mt-3 text-base font-black text-primary-600 dark:text-primary-400">{{ $t->price > 0 ? fa_digits(number_format((float) $t->price)).' تومان' : 'رایگان' }}</p>
                </button>
            @empty
                <div class="col-span-3 p-10 text-center glass-card">
                    <p class="text-sm text-slate-500 dark:text-slate-400">هنوز نوع مشاوره‌ای ثبت نشده است.</p>
                </div>
            @endforelse
        </div>

    {{-- ===== مرحله ۲: جدول زمان‌بندی زنده ===== --}}
    @elseif ($step === 2)
        <div wire:key="step-datetime">
            <h3 class="flex items-center gap-2 mb-3 font-bold text-slate-900 dark:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                تاریخ را انتخاب کنید
            </h3>
            <div class="grid grid-cols-4 gap-2 mb-5 sm:grid-cols-7">
                @foreach ($calendar as $day)
                    <button type="button" wire:click="pickDate('{{ $day['date'] }}')"
                            wire:key="day-{{ $day['date'] }}"
                            class="relative rounded-2xl border p-2.5 text-center transition {{ $date === $day['date'] ? 'border-primary-500 bg-primary-50 shadow-lg shadow-primary-500/20 dark:bg-primary-950/50' : 'border-slate-200 hover:border-primary-300 dark:border-slate-700' }} {{ $day['free_count'] === 0 ? 'opacity-45' : '' }}">
                        <p class="text-[10px] font-medium text-slate-500">{{ $day['day_label'] }}</p>
                        <p class="my-0.5 text-lg font-black {{ $day['free_count'] > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-400' }}">{{ $day['display_day'] }}</p>
                        <p class="text-[9px] font-medium text-slate-400">{{ $day['display_month'] }}</p>
                        <span class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[9px] font-bold {{ $day['free_count'] > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-500 dark:bg-rose-950/40 dark:text-rose-400' }}">
                            @if ($day['free_count'] > 0) ✓{{ fa_digits($day['free_count']) }} @else ✕ پر @endif
                        </span>
                    </button>
                @endforeach
            </div>

            @if ($date !== '')
                <div wire:key="slots-{{ $date }}" class="space-y-3">
                    <h3 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        ساعت خالی — {{ fa_digits(\Carbon\CarbonImmutable::parse($date)->format('d/m/Y')) }}
                        <span class="text-xs font-normal text-slate-400">(زنده)</span>
                    </h3>

                    @if (count($slots))
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                            @foreach ($slots as $s)
                                <button type="button" wire:click="chooseSlot({{ $s['id'] }})" wire:key="slot-{{ $s['id'] }}"
                                        class="rounded-xl border p-2.5 text-center text-sm font-bold transition {{ $slotId === $s['id'] ? 'border-primary-500 bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'border-slate-200 text-slate-700 hover:border-primary-300 dark:border-slate-700 dark:text-slate-200' }}"
                                        dir="ltr">{{ $s['start'] }}</button>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center glass-card">
                            <p class="text-sm font-bold text-rose-500">تایم مشاوره خالی نیست</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">تمام زمان‌های این روز رزرو شده‌اند — روز دیگری را انتخاب کنید.</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="p-6 text-center glass-card">
                    <p class="text-xs text-slate-500 dark:text-slate-400">برای دیدن زمان‌های خالی، یک تاریخ از تقویم بالا انتخاب کنید.</p>
                </div>
            @endif

            <div class="flex gap-3 mt-5">
                <button type="button" wire:click="nextStep" class="btn-primary !px-5 !py-2.5 !text-xs">ادامه</button>
                <button type="button" wire:click="prevStep" class="btn-outline !px-4 !py-2.5 !text-xs">بازگشت</button>
            </div>
        </div>

    {{-- ===== مرحله ۳: اطلاعات فردی ===== --}}
    @elseif ($step === 3)
        <div wire:key="step-info">
            <form wire:submit="nextStep" class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">نام</label>
                    <input type="text" wire:model="contactName"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('contactName') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">تلفن همراه</label>
                    <input type="tel" wire:model="contactPhone" dir="ltr" placeholder="09121234567"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('contactPhone') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">ایمیل (اختیاری)</label>
                    <input type="email" wire:model="contactEmail" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('contactEmail') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">توضیحات (اختیاری)</label>
                    <textarea wire:model="notes" rows="3"
                              class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100"></textarea>
                </div>
            </form>

            <div class="flex gap-3 mt-5">
                <button type="button" wire:click="nextStep" class="btn-primary !px-5 !py-2.5 !text-xs">ادامه</button>
                <button type="button" wire:click="prevStep" class="btn-outline !px-4 !py-2.5 !text-xs">بازگشت</button>
            </div>
        </div>

    {{-- ===== مرحله ۴: تایید نهایی ===== --}}
    @elseif ($step === 4)
        <div wire:key="step-success" class="p-8 text-center glass-card">
            <span class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-3xl rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">✓</span>
            <h3 class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ $successMessage }}</h3>

            @if ($lastBooking)
                <div class="max-w-sm p-5 mx-auto mt-6 space-y-2 text-sm rounded-2xl bg-slate-50 dark:bg-slate-800/60">
                    <div class="flex justify-between"><span class="text-slate-500">مشاور</span><b>{{ $lastBooking->consultant?->user?->name }}</b></div>
                    <div class="flex justify-between"><span class="text-slate-500">نوع</span><b>{{ $lastBooking->type?->name }}</b></div>
                    <div class="flex justify-between"><span class="text-slate-500">تاریخ</span><b>{{ $lastBooking->slot?->date ? jdate('Y/m/d', $lastBooking->slot->date) : '—' }}</b></div>
                    <div class="flex justify-between"><span class="text-slate-500">ساعت</span><b dir="ltr">{{ substr($lastBooking->slot?->start_time ?? '', 0, 5) }}</b></div>
                    @if ($lastBooking->order)
                        <div class="flex justify-between"><span class="text-slate-500">شماره سفارش</span><b dir="ltr">{{ $lastBooking->order->order_number }}</b></div>
                    @endif
                </div>
            @endif

            <div class="flex flex-wrap justify-center gap-3 mt-6">
                <a href="{{ url('/dashboard?tab=consultations') }}" class="btn-primary !px-5 !py-2.5 !text-xs">مشاوره‌های من</a>
                <button type="button" wire:click="prevStep" class="btn-outline !px-5 !py-2.5 !text-xs">رزرو دوباره</button>
            </div>
        </div>
    @endif
</div>
