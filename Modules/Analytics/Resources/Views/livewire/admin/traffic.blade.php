<div>
    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        {{-- کاربران آنلاین --}}
        <section class="chart-card">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold">کاربران آنلاین</h3>
                <span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-500">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    {{ fa_digits($onlineUsers->count()) }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse ($onlineUsers as $activity)
                    <div class="flex items-center gap-3" wire:key="online-{{ $activity->id }}">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                            <x-icon name="user" size="4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $activity->user?->name ?? 'مهمان' }}</p>
                            <p class="truncate text-[11px] text-slate-400" dir="ltr">{{ $activity->page }}</p>
                        </div>
                        <span class="shrink-0 text-[11px] text-slate-400">{{ $activity->last_active_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">هیچ کاربر آنلاینی وجود ندارد.</p>
                @endforelse
            </div>
        </section>

        {{-- صفحات پر بازدید --}}
        <section class="chart-card lg:col-span-2">
            <h3 class="mb-4 text-lg font-bold">صفحات پر بازدید</h3>
            <div class="space-y-2.5">
                @forelse ($topPages as $index => $page)
                    <div class="flex items-center gap-3" wire:key="toppage-{{ $index }}">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ fa_digits($index + 1) }}</span>
                        <span class="truncate font-mono text-xs text-slate-600 dark:text-slate-300" dir="ltr">{{ $page->page }}</span>
                        <span class="mr-auto shrink-0 rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">{{ fa_digits($page->visits) }} بازدید</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">هنوز بازدیدی ثبت نشده است.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- روند ثبت‌نام --}}
    <section class="chart-card mb-8">
        <h3 class="mb-4 text-lg font-bold">روند ثبت‌نام کاربران (۶ ماه)</h3>
        <div class="h-64">
            <canvas id="usersGrowthChart" aria-label="نمودار روند ثبت‌نام کاربران"></canvas>
        </div>
    </section>

    {{-- آخرین فعالیت‌ها — کارت‌های موبایل‌پسند --}}
    <h3 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">آخرین فعالیت‌ها</h3>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($recentActivity as $activity)
            <div class="glass-card glass-card-hover flex items-center gap-3 p-4" wire:key="activity-{{ $activity->id }}">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-xs font-bold text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">
                    {{ mb_substr($activity->user?->name ?? '؟', 0, 1) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $activity->user?->name ?? 'مهمان' }}</p>
                    <p class="truncate text-[11px] text-slate-500 dark:text-slate-400" dir="ltr">{{ $activity->page }}</p>
                </div>
                <div class="shrink-0 text-left">
                    <p class="font-mono text-[10px] text-slate-400" dir="ltr">{{ $activity->ip_address ?? '' }}</p>
                    <p class="text-[10px] text-slate-400">{{ $activity->last_active_at?->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <div class="glass-card col-span-full p-10 text-center text-sm text-slate-500 dark:text-slate-400">فعالیتی ثبت نشده است.</div>
        @endforelse
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    function toPersianNum(n) { return String(n).replace(/\d/g, (d) => faDigits[+d]); }

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.2)';

    Chart.defaults.font.family = 'Vazirmatn, Tahoma, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = textColor;

    new Chart(document.getElementById('usersGrowthChart'), {
        type: 'bar',
        data: {
            labels: @json($usersChart['labels']),
            datasets: [{
                label: 'کاربران جدید',
                data: @json($usersChart['data']),
                backgroundColor: 'rgba(6, 182, 212, 0.75)',
                borderColor: '#06b6d4',
                borderWidth: 2,
                borderRadius: 10,
                maxBarThickness: 48,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', rtl: true, labels: { color: textColor, usePointStyle: true, padding: 16 } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0, callback: (v) => toPersianNum(v) } },
            },
            animation: { duration: 1400, easing: 'easeOutQuart' },
        },
    });
});
</script>
@endpush
