<div>
    @if (session('success'))
        <div class="mb-5 p-4 text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ session('success') }}</div>
    @endif

    {{-- سربرگ + دکمه درخواست مشاوره — الگوی projects/index --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">مشاوره‌های من</h2>
        <a href="{{ route('public.consultation.index') }}" class="btn-primary !px-5 !py-2.5 !text-xs">
            <x-icon name="plus" size="4" />
            درخواست مشاوره جدید
        </a>
    </div>

    {{-- فیلتر وضعیت --}}
    <div class="mb-5 flex flex-wrap gap-2">
        <button type="button" wire:click="$set('status', '')" class="skill-tag !py-1.5 !px-4 !text-xs {{ $status === '' ? 'text-primary-600 border-primary-300 bg-primary-50 dark:bg-primary-950/50 dark:text-primary-400' : '' }}">همه</button>
        @foreach (['pending' => 'در انتظار', 'confirmed' => 'تایید شده', 'done' => 'انجام شده', 'cancelled' => 'لغو شده', 'no_show' => 'عدم حضور'] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')" class="skill-tag !py-1.5 !px-4 !text-xs {{ $status === $key ? 'text-primary-600 border-primary-300 bg-primary-50 dark:bg-primary-950/50 dark:text-primary-400' : '' }}">{{ $label }}</button>
        @endforeach
    </div>

    {{-- لیست رزروهای من --}}
    <div class="space-y-4">
        @forelse ($consultations as $c)
            <div class="glass-card p-5" wire:key="my-cons-{{ $c->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold bg-{{ $c->statusEnum()->color() }}-100 text-{{ $c->statusEnum()->color() }}-700 dark:bg-{{ $c->statusEnum()->color() }}-950/60 dark:text-{{ $c->statusEnum()->color() }}-400">{{ $c->statusEnum()->label() }}</span>
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-500 dark:bg-slate-800">{{ $c->modeEnum()->label() }}</span>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white">{{ $c->type?->name }} — {{ $c->consultant?->user?->name }}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            تاریخ: <span>{{ $c->slot?->date ? jdate('Y/m/d', $c->slot->date) : '—' }}</span>
                            · ساعت: <span dir="ltr">{{ substr($c->slot?->start_time ?? '', 0, 5) }}</span>
                            @if ($c->order)
                                · سفارش: <span dir="ltr">{{ $c->order->order_number }}</span>
                            @endif
                        </p>
                        @if ($c->notes)
                            <p class="mt-2 text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($c->notes, 120) }}</p>
                        @endif
                    </div>
                    @if (in_array($c->status, ['pending', 'confirmed'], true))
                        <button type="button" wire:click="cancel({{ $c->id }})" wire:confirm="لغو رزرو و آزاد شدن تایم؟"
                                class="btn-outline !px-3.5 !py-2 !text-xs !border-rose-200 !text-rose-500">لغو رزرو</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="glass-card p-10 text-center">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">هنوز مشاوره‌ای رزرو نکرده‌اید.</p>
                <a href="{{ route('public.consultation.index') }}" class="btn-primary !px-6 !py-3 !text-xs">ثبت اولین درخواست مشاوره</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $consultations->links() }}</div>
</div>