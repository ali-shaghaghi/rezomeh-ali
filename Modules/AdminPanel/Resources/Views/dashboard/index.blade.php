@extends('adminpanel::layouts.master')

@section('title', 'داشبورد')

@section('content')
    <!-- Title -->
    <div class="mb-8 opacity-0-start observe-in">
        <h2 class="text-2xl font-bold sm:text-3xl">داشبورد</h2>
        <p class="mt-1 text-slate-500 dark:text-slate-400">{{ now()->format('Y/m/d') }}</p>
    </div>

    <!-- Stat Cards -->
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin-stat-card
            title="درآمد کل"
            :count="$stats['total_revenue'] / 1000000"
            suffix=" میلیون"
            icon="wallet"
            color="primary"
            :trend="5"
            :delay="0"
            class="stagger-1"
        />

        <x-admin-stat-card
            title="کل سفارشات"
            :count="$stats['total_orders']"
            icon="cart"
            color="violet"
            :trend="8"
            :delay="100"
            class="stagger-2"
        />

        <x-admin-stat-card
            title="تعداد کاربران"
            :count="$stats['total_users']"
            icon="users"
            color="cyan"
            :trend="3"
            :delay="200"
            class="stagger-3"
        />

        <x-admin-stat-card
            title="تیکت‌های باز"
            :count="$stats['open_tickets']"
            icon="mail"
            color="amber"
            :trend="$stats['open_tickets'] > 0 ? 0 : -5"
            :delay="300"
            class="stagger-4"
        />
    </div>

    <!-- Charts -->
    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="chart-card opacity-0-start observe-in lg:col-span-2">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold">روند درآمد</h3>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-medium text-white shadow-md shadow-primary-500/30">ماهانه</button>
                    <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-700">سالانه</button>
                </div>
            </div>
            <div class="h-64 sm:h-72">
                <canvas id="revenueChart" aria-label="نمودار درآمد"></canvas>
            </div>
        </section>

        <section class="chart-card opacity-0-start observe-in">
            <h3 class="mb-4 text-lg font-bold">وضعیت سفارشات</h3>
            <div class="mx-auto h-56 max-w-xs sm:h-64">
                <canvas id="orderStatusChart" aria-label="نمودار وضعیت سفارشات"></canvas>
            </div>
        </section>
    </div>

    {{-- ترافیک هفتگی — داینامیک از سیستم ردیابی --}}
    <section class="chart-card opacity-0-start observe-in mb-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-bold">ترافیک هفتگی</h3>
            <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-400">
                بازدیدهای ۷ روز گذشته
            </span>
        </div>
        <div class="h-56 sm:h-64">
            <canvas id="weeklyTrafficChart" aria-label="نمودار ترافیک هفتگی"></canvas>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <!-- Recent Orders -->
        <section class="chart-card min-w-0 opacity-0-start observe-in xl:col-span-2">
            <h3 class="mb-4 text-lg font-bold">آخرین سفارشات</h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @forelse($recentOrders as $order)
                    <div class="glass-card glass-card-hover flex min-w-0 items-center gap-3 p-4" wire:key="dash-order-{{ $order->id }}">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-sm font-bold text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            {{ mb_substr($order->user?->name ?? '؟', 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $order->user?->name ?? 'کاربر حذف‌شده' }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $order->title }}</p>
                        </div>
                        <div class="shrink-0 text-left">
                            <p class="text-sm font-black text-slate-900 dark:text-white" dir="ltr">{{ number_format($order->amount) }}</p>
                            <span class="rounded-full bg-{{ $order->status_color }}-100 px-2 py-0.5 text-[10px] font-medium text-{{ $order->status_color }}-700 dark:bg-{{ $order->status_color }}-900/40 dark:text-{{ $order->status_color }}-300">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="glass-card col-span-full p-8 text-center text-sm text-slate-500">سفارشی یافت نشد</div>
                @endforelse
            </div>
        </section>

        <!-- Recent Activity — داینامیک از سیستم ردیابی -->
        <section class="chart-card min-w-0 opacity-0-start observe-in">
            <h3 class="mb-4 text-lg font-bold">فعالیت‌های اخیر</h3>
            <ul class="max-h-96 space-y-3 overflow-y-auto pl-1">
                @forelse($recentActivity as $activity)
                    <li class="flex gap-3 rounded-xl p-2 transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30" wire:key="dash-activity-{{ $activity->id }}">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-xs font-bold text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">
                            {{ mb_substr($activity->user?->name ?? '؟', 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $activity->user?->name ?? 'مهمان' }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $activity->page }}</p>
                        </div>
                        <span class="shrink-0 text-[10px] text-slate-400">{{ $activity->last_active_at?->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-sm text-slate-500">فعالیتی ثبت نشده</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/*
| نمودارهای داشبورد ادمین.
| باگ قبلی: init فقط روی DOMContentLoaded بود؛ ورود از صفحه لاگین با
| wire:navigate انجام می‌شود (SPA) و DOMContentLoaded دوباره شلیک نمی‌شود
| → نمودارها خالی می‌ماندند و کاربر مجبور بود یک بار دیگر رفرش کند.
| حالا init روی هر سه مسیر اجرا می‌شود: بارگذاری کامل + SPA + لود دیرهنگام CDN.
*/
(function () {
    const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    function toPersianNum(n) {
        return String(n).replace(/\d/g, (d) => faDigits[+d]);
    }

    function initDashboardCharts() {
        // CDN نمودار هنوز نرسیده — کمی بعد دوباره تلاش کن
        if (typeof window.Chart === 'undefined') {
            setTimeout(initDashboardCharts, 150);
            return;
        }

        const revenueCanvas = document.getElementById('revenueChart');
        if (!revenueCanvas) return; // این صفحه داشبورد نیست (ناوبری به صفحه دیگر)

        // جلوگیری از رسم تکراری روی یک canvas
        if (revenueCanvas.dataset.charted) return;
        revenueCanvas.dataset.charted = '1';

        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.2)';
        const tooltipBg = isDark ? '#1e293b' : '#ffffff';
        const tooltipBorder = isDark ? '#334155' : '#e2e8f0';

        Chart.defaults.font.family = 'Vazirmatn, Tahoma, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;

    // Revenue Chart — داده واقعی از دیتابیس
    const revenueLabels = @json($monthlyRevenueLabels);
    const revenueData = @json($monthlyRevenueData);
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [
                {
                    label: 'درآمد (میلیون تومان)',
                    data: revenueData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.12)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: isDark ? '#1e293b' : '#fff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', rtl: true, labels: { color: textColor, usePointStyle: true, padding: 16 } },
                tooltip: {
                    rtl: true,
                    backgroundColor: tooltipBg,
                    titleColor: textColor,
                    bodyColor: textColor,
                    borderColor: tooltipBorder,
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 12,
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: { grid: { color: gridColor }, ticks: { color: textColor, callback: (v) => toPersianNum(v) } },
            },
            animation: { duration: 1500, easing: 'easeOutQuart' },
        },
    });

    // Weekly Traffic Chart — داده واقعی از سیستم ردیابی
    new Chart(document.getElementById('weeklyTrafficChart'), {
        type: 'line',
        data: {
            labels: @json($weeklyTrafficLabels),
            datasets: [{
                label: 'بازدید صفحات',
                data: @json($weeklyTrafficData),
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.12)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: isDark ? '#1e293b' : '#fff',
                pointBorderColor: '#06b6d4',
                pointBorderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', rtl: true, labels: { color: textColor, usePointStyle: true, padding: 16 } },
                tooltip: { rtl: true, backgroundColor: tooltipBg, titleColor: textColor, bodyColor: textColor, borderColor: tooltipBorder, borderWidth: 1, padding: 12, cornerRadius: 12 },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: { grid: { color: gridColor }, ticks: { color: textColor, callback: (v) => toPersianNum(v) }, beginAtZero: true },
            },
            animation: { duration: 1500, easing: 'easeOutQuart' },
        },
    });

    // Order Status Chart
    new Chart(document.getElementById('orderStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['در انتظار', 'بررسی', 'پذیرفته', 'توسعه', 'تکمیل', 'لغو'],
            datasets: [{
                data: [5, 3, 2, 3, 2, 0],
                backgroundColor: ['#f59e0b', '#3b82f6', '#6366f1', '#8b5cf6', '#10b981', '#6b7280'],
                borderWidth: 0,
                hoverOffset: 12,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { position: 'bottom', rtl: true, labels: { color: textColor, padding: 14, usePointStyle: true } } },
            animation: { animateRotate: true, duration: 1400 },
        },
    });
}

    // ۱) بارگذاری کامل صفحه  ۲) ورود SPA از لاگین (livewire:navigated)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }
    document.addEventListener('livewire:navigated', initDashboardCharts);
})();
</script>
@endpush