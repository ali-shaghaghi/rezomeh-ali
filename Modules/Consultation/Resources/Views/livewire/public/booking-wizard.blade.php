<div>
    {{-- ===== نوار مراحل ===== --}}
    <div class="flex items-center justify-between gap-1 mb-6" wire:key="wizard-steps">
        @foreach ([__('consultation.steps.0'), __('consultation.steps.1'), __('consultation.steps.2'), __('consultation.steps.3'), __('consultation.steps.4'), __('consultation.steps.5')] as $i => $label)
            <div class="flex flex-1 items-center {{ $i < 5 ? 'flex-1' : '' }}">
                <button type="button" wire:click="prevStep" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition {{ $step >= $i + 1 ? 'bg-primary-600 text-white' : 'bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}"
                        {{ $step <= $i + 1 ? 'disabled' : '' }}>{{ fa_digits($i + 1) }}</button>
                <span class="mr-2 hidden text-xs font-medium text-slate-500 sm:inline {{ $step >= $i + 1 ? '!text-primary-600 dark:!text-primary-400' : '' }}">{{ $label }}</span>
                @if ($i < 5)
                    <div class="mx-2 h-0.5 flex-1 {{ $step > $i + 1 ? 'bg-primary-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    @if ($errors->any())
        <div class="p-4 mb-5 border rounded-2xl border-rose-200 bg-rose-50 dark:border-rose-800 dark:bg-rose-950/40">
            @foreach ($errors->all() as $error)
                <p class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- راهنمای ورود — رزرو فقط برای کاربران واردشده --}}
    @if (! auth()->check())
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 mb-5 border rounded-2xl border-rose-200 bg-rose-50 dark:border-rose-800 dark:bg-rose-950/40">
            <p class="text-xs font-bold text-rose-600 dark:text-rose-400">برای رزرو مشاوره ابتدا باید وارد حساب کاربری شوید.</p>
            <a href="{{ route('auth.login') }}" class="btn-primary !px-4 !py-2 !text-xs">ورود / ثبت‌نام</a>
        </div>
    @endif

    {{-- ===== مرحله ۱: انتخاب مشاور + مودال جزئیات (ریل‌تایم، بدون رفرش) ===== --}}
    @if ($step === 1)
        <div class="grid gap-4 sm:grid-cols-2" wire:key="step-consultants"
             x-data="{ details: null }" @keydown.escape.window="if (details) details = null">
            @forelse ($consultants as $c)
                @php
                    $photo = $c->avatar
                        ? asset('storage/'.$c->avatar)
                        : ($c->user?->avatar ? user_avatar_url($c->user) : null);
                    $detailsPayload = [
                        'id' => $c->id,
                        'name' => $c->personName(),
                        'isAdmin' => $c->admin_id !== null,
                        'photo' => $photo,
                        'specialty' => $c->specialty,
                        'bio' => $c->bio ?? '',
                        'rating' => (float) $c->rating,
                        'hourly' => $c->hourly_rate > 0 ? __('consultation.per_hour', ['price' => format_price_for_locale((float) $c->hourly_rate)]) : __('consultation.call_us'),
                    ];
                @endphp
                <div wire:key="consultant-{{ $c->id }}" role="button" tabindex="0"
                     @click="$wire.chooseConsultant({{ $c->id }})"
                     @keydown.enter="$wire.chooseConsultant({{ $c->id }})"
                     class="relative flex flex-col gap-5 p-6 mb-4 group glass-card reveal sm:flex-row sm:items-center post-card animate-slide-up cursor-pointer">
                    <div class="flex items-start gap-4">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $c->personName() }}" class="h-14 w-14 shrink-0 rounded-2xl object-cover">
                        @else
                            <span class="flex items-center justify-center text-xl font-black text-white h-14 w-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500">
                                {{ mb_substr($c->personName(), 0, 1) }}
                            </span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="flex items-center gap-2 font-bold text-slate-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                {{ $c->personName() }}
                                @if ($c->admin_id)
                                    <span class="rounded-md bg-accent-100 px-1.5 py-0.5 text-[10px] font-bold text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">ادمین</span>
                                @endif
                            </h3>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $c->specialty }}</p>
                            @if ($c->rating > 0)
                                <p class="mt-1.5 text-xs font-bold text-amber-500">★ {{ fa_digits($c->rating) }} <span class="font-normal text-slate-400">({{ __('consultation.rating_users') }})</span></p>
                            @endif
                            @if ($c->bio)
                                <p class="mt-1 text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($c->bio, 90) }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-400">{{ $c->hourly_rate > 0 ? __('consultation.per_hour', ['price' => format_price_for_locale((float) $c->hourly_rate)]) : __('consultation.call_us') }}</p>
                        </div>
                    </div>

                    <span @click.stop="details = {{ \Illuminate\Support\Js::from($detailsPayload) }}"
                          class="absolute bottom-3 left-4 inline-flex items-center gap-1 rounded-lg bg-primary-50 px-2.5 py-1 text-[11px] font-bold text-primary-600 transition hover:bg-primary-100 dark:bg-primary-950/40 dark:text-primary-400 dark:hover:bg-primary-950/70">
                        {{ __('consultation.consultant_details') }}
                    </span>
                </div>
            @empty
                <div class="col-span-2 p-10 text-center glass-card">
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('consultation.no_consultants') }}</p>
                </div>
            @endforelse

            {{-- مودال جزئیات مشاور — کلیک در هر نقطه بیرون آن را می‌بندد --}}
            <template x-if="details">
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                     @click="details = null">
                    <div class="w-full max-w-lg p-6 bg-white border shadow-2xl rounded-3xl border-slate-200/60 animate-slide-up dark:border-slate-700/60 dark:bg-surface-800"
                         @click.stop>
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <template x-if="details.photo">
                                    <img :src="details.photo" :alt="details.name" class="h-20 w-20 shrink-0 rounded-2xl object-cover">
                                </template>
                                <template x-if="! details.photo">
                                    <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 text-3xl font-black text-white"
                                          x-text="details.name.slice(0, 1)"></span>
                                </template>
                                <div class="min-w-0">
                                    <h3 class="flex items-center gap-2 text-lg font-black text-slate-900 dark:text-white">
                                        <span x-text="details.name"></span>
                                        <span x-show="details.isAdmin" style="display: none"
                                              class="rounded-md bg-accent-100 px-1.5 py-0.5 text-[10px] font-bold text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">{{ __('consultation.admin_badge') }}</span>
                                    </h3>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400" x-text="details.specialty"></p>
                                </div>
                            </div>
                            <button type="button" @click="details = null" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-2 dark:bg-amber-950/30">
                                <span class="text-sm font-black text-amber-500">
                                    <template x-for="i in 5" :key="i"><span x-text="i <= Math.round(details.rating) ? '★' : '☆'"></span></template>
                                </span>
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400" x-text="details.rating.toFixed(1)"></span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('consultation.rating_users') }}</span>
                            </div>

                            <div class="mt-4">
                                <p class="mb-1.5 text-xs font-black text-slate-500 dark:text-slate-400">{{ __('consultation.about_consultant') }}</p>
                                <p class="whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300" x-text="details.bio || '—'"></p>
                            </div>

                            <p class="mt-4 text-sm font-bold text-primary-600 dark:text-primary-400" x-text="details.hourly"></p>
                        </div>

                        <div class="mt-5 flex gap-3">
                            <button type="button"
                                    @click="var id = details.id; details = null; $wire.chooseConsultant(id)"
                                    class="btn-primary !px-5 !py-2.5 !text-xs flex-1">{{ __('consultation.choose_this') }}</button>
                            <button type="button" @click="details = null"
                                    class="btn-outline !px-4 !py-2.5 !text-xs">{{ __('consultation.close') }}</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

    {{-- ===== مرحله ۲: نوع مشاوره ===== --}}
    @elseif ($step === 2)
        <div class="grid gap-4 sm:grid-cols-2" wire:key="step-types">
            @foreach ($types as $t)
                <button type="button" wire:click="chooseType({{ $t->id }})" wire:key="type-{{ $t->id }}"
                        class="relative flex flex-col gap-2 p-5 text-right transition glass-card-hover group glass-card reveal animate-slide-up">
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $t->localizedName() }}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('consultation.duration', ['minutes' => fa_digits($t->duration_minutes)]) }}</p>
                    @if ($t->description)
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($t->localizedDescription() ?? '', 80) }}</p>
                    @endif
                    <p class="mt-3 text-lg font-black text-primary-600 dark:text-primary-400">{{ $t->price > 0 ? format_price_for_locale((float) $t->price) : __('consultation.free') }}</p>
                </button>
            @endforeach
        </div>
        <button type="button" wire:click="prevStep" class="btn-outline mt-5 !px-4 !py-2 !text-xs">{{ __('consultation.back') }}</button>

    {{-- ===== مرحله ۳: تاریخ و اسلات ===== --}}
    @elseif ($step === 3)
        <div wire:key="step-datetime">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">{{ __('consultation.pick_date') }}</h3>
            <div class="grid grid-cols-4 gap-2 mb-6 sm:grid-cols-7">
                @foreach ($calendar as $day)
                    <button type="button" wire:click="pickDate('{{ $day['date'] }}')"
                            wire:key="day-{{ $day['date'] }}"
                            class="rounded-xl border p-2.5 text-center transition {{ $date === $day['date'] ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/50' : 'border-slate-200 dark:border-slate-700' }} {{ $day['free_count'] === 0 ? 'opacity-40' : '' }}">
                        <p class="text-[10px] text-slate-500">{{ $day['day_label'] }}</p>
                        <p class="text-lg font-black {{ $day['free_count'] > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-400' }}">{{ $day['display_day'] }}</p>
                        <p class="text-[9px] font-medium text-slate-400">{{ $day['display_month'] }}</p>
                        <p class="text-[10px] {{ $day['free_count'] > 0 ? 'text-emerald-500' : 'text-rose-400' }}">{{ $day['free_count'] > 0 ? __('consultation.slots_free', ['count' => fa_digits($day['free_count'])]) : __('consultation.full') }}</p>
                    </button>
                @endforeach
            </div>

            @if ($date !== '')
                <h3 class="mb-3 font-bold text-slate-900 dark:text-white">{{ __('consultation.pick_time') }}</h3>
                @if (count($slots))
                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                        @foreach ($slots as $s)
                            <button type="button" wire:click="chooseSlot({{ $s['id'] }})" wire:key="slot-{{ $s['id'] }}"
                                    class="rounded-xl border p-2.5 text-center text-sm font-bold transition {{ $slotId === $s['id'] ? 'border-primary-500 bg-primary-500 text-white' : 'border-slate-200 text-slate-700 dark:border-slate-700 dark:text-slate-200' }}"
                                    dir="ltr">{{ $s['start'] }}</button>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center glass-card">
                        <p class="text-sm font-bold text-rose-500">{{ __('consultation.no_slots_title') }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('consultation.no_slots_desc') }}</p>
                    </div>
                @endif
            @endif

            <div class="flex gap-3 mt-5">
                <button type="button" wire:click="nextStep" class="btn-primary !px-5 !py-2.5 !text-xs">{{ __('consultation.continue') }}</button>
                <button type="button" wire:click="prevStep" class="btn-outline !px-4 !py-2.5 !text-xs">{{ __('consultation.back') }}</button>
            </div>
        </div>

    {{-- ===== مرحله ۴: اطلاعات فردی ===== --}}
    @elseif ($step === 4)
        <div wire:key="step-info">
            <form wire:submit="nextStep" class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('consultation.name') }}</label>
                    <input type="text" wire:model="contactName"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('contactName') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('consultation.phone') }}</label>
                    <input type="tel" wire:model="contactPhone" dir="ltr" placeholder="09121234567"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('contactPhone') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('consultation.email_optional') }}</label>
                    <input type="email" wire:model="contactEmail" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('contactEmail') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('consultation.notes_optional') }}</label>
                    <textarea wire:model="notes" rows="3"
                              class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100"></textarea>
                </div>
            </form>

            <div class="flex gap-3 mt-5">
                <button type="button" wire:click="nextStep" class="btn-primary !px-5 !py-2.5 !text-xs">{{ __('consultation.continue') }}</button>
                <button type="button" wire:click="prevStep" class="btn-outline !px-4 !py-2.5 !text-xs">{{ __('consultation.back') }}</button>
            </div>
        </div>

    {{-- ===== مرحله ۵: پرداخت (تایید نهایی + ساخت سفارش) ===== --}}
    @elseif ($step === 5)
        <div wire:key="step-payment">
            <div class="p-6 glass-card">
                <h3 class="mb-4 font-bold text-slate-900 dark:text-white">{{ __('consultation.booking_summary') }}</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('consultation.consultant') }}</dt><dd class="font-bold">{{ $selectedConsultant?->personName() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('consultation.type') }}</dt><dd class="font-bold">{{ $selectedType?->localizedName() }} ({{ $selectedType ? __('consultation.minute', ['count' => fa_digits($selectedType->duration_minutes)]) : '' }})</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('consultation.date') }}</dt><dd class="font-bold" dir="ltr">{{ $date }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('consultation.time') }}</dt><dd class="font-bold" dir="ltr">{{ $slotStart }} - {{ $slotEnd }}</dd></div>
                    <div class="flex justify-between pt-3 mt-3 border-t border-slate-200 dark:border-slate-700">
                        <dt class="font-bold text-slate-900 dark:text-white">{{ __('consultation.payable') }}</dt>
                        <dd class="text-lg font-black text-primary-600 dark:text-primary-400">{{ $selectedType && $selectedType->price > 0 ? format_price_for_locale((float) $selectedType->price) : __('consultation.free') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="button" wire:click="confirmBooking" wire:loading.attr="disabled" wire:target="confirmBooking"
                        class="btn-primary !px-6 !py-3 !text-xs">
                    {{ __('consultation.confirm_booking') }}
                </button>
                <button type="button" wire:click="prevStep" class="btn-outline !px-4 !py-2.5 !text-xs">{{ __('consultation.back') }}</button>
            </div>
        </div>

    {{-- ===== مرحله ۶: تایید ===== --}}
    @elseif ($step === 6)
        <div wire:key="step-success" class="p-10 text-center glass-card">
            <span class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-3xl rounded-full bg-emerald-100 dark:bg-emerald-950/60">✓</span>
            <h3 class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ $successMessage }}</h3>
            @if ($lastBooking)
                <div class="max-w-sm p-5 mx-auto mt-6 space-y-2 text-sm rounded-2xl bg-slate-50 dark:bg-slate-800/60">
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('consultation.consultant') }}</span><b>{{ $lastBooking->consultant?->personName() }}</b></div>
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('consultation.type') }}</span><b>{{ $lastBooking->type?->localizedName() }}</b></div>
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('consultation.date') }}</span><b>{{ $lastBooking->slot?->date ? (current_locale() === 'fa' ? jdate('Y/m/d', $lastBooking->slot->date) : fa_digits($lastBooking->slot->date->format('Y/m/d'))) : '—' }}</b></div>
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('consultation.time') }}</span><b dir="ltr">{{ substr($lastBooking->slot?->start_time ?? '', 0, 5) }}</b></div>
                    @if ($lastBooking->order)
                        <div class="flex justify-between"><span class="text-slate-500">{{ __('consultation.order_number') }}</span><b dir="ltr">{{ $lastBooking->order->order_number }}</b></div>
                    @endif
                </div>
            @endif
            <div class="flex flex-wrap justify-center gap-3 mt-6">
                <a href="{{ url('/dashboard?tab=consultations') }}" class="btn-primary !px-5 !py-2.5 !text-xs">{{ __('consultation.my_consultations') }}</a>
                <a href="{{ route('public.home') }}" class="btn-outline !px-5 !py-2.5 !text-xs">{{ __('consultation.back_home') }}</a>
            </div>
        </div>
    @endif
</div>