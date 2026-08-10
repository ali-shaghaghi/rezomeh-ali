@extends('admin::layouts.master')

@section('title', 'داشبورد')

@section('content')
    <!-- Title -->
    <div class="mb-8 opacity-0-start observe-in">
        <h2 class="text-2xl font-bold sm:text-3xl">داشبورد</h2>
        <p class="mt-1 text-slate-500 dark:text-slate-400">{{ now()->format('Y/m/d') }}</p>
    </div>

    <!-- Stat Cards -->
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin-stat-card
            title="درآمد ماهانه"
            :count="128"
            suffix=" میلیون"
            icon="wallet"
            color="primary"
            :trend="5"
            :delay="0"
            class="stagger-1"
        />

        <x-admin-stat-card
            title="سفارشات جدید"
            :count="47"
            icon="cart"
            color="violet"
            :trend="8"
            :delay="100"
            class="stagger-2"
        />

        <x-admin-stat-card
            title="بازدیدکنندگان"
            :count="3842"
            icon="eye"
            color="cyan"
            :trend="-2"
            :delay="200"
            class="stagger-3"
        />

        <x-admin-stat-card
            title="رضایت مشتری"
            :count="92"
            suffix="٪"
            icon="star"
            color="amber"
            :trend="0"
            :delay="300"
            class="stagger-4"
        />
    </div>

    <!-- Charts -->
    <div class="mb-8 grid gap-6 lg:grid-cols-3">
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
            <h3 class="mb-4 text-lg font-bold">دسته‌بندی فروش</h3>
            <div class="mx-auto h-56 max-w-xs sm:h-64">
                <canvas id="categoryChart" aria-label="نمودار دایره‌ای"></canvas>
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <!-- Traffic -->
        <section class="chart-card opacity-0-start observe-in xl:col-span-2">
            <h3 class="mb-4 text-lg font-bold">ترافیک هفتگی</h3>
            <div class="h-56 sm:h-64">
                <canvas id="trafficChart" aria-label="نمودار میله‌ای"></canvas>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="chart-card opacity-0-start observe-in">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold">فعالیت اخیر</h3>
                <button type="button" class="text-slate-400 hover:text-primary-500" aria-label="گزینه‌ها">
                    <x-icon name="dots" />
                </button>
            </div>
            <ul class="space-y-4">
                <li class="flex gap-3 rounded-xl p-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40">
                        <x-icon name="cart" size="4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium">پرداخت #۴۸۲۱</p>
                        <p class="text-xs text-slate-500">محمد رضایی · ۴۵,۲۰۰,۰۰۰ تومان</p>
                    </div>
                </li>
                <li class="flex gap-3 rounded-xl p-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40">
                        <x-icon name="users" size="4" />
                    </span>
                    <div>
                        <p class="text-sm font-medium">موجودی کم</p>
                        <p class="text-xs text-slate-500">هدفون Sony — ۳ عدد</p>
                    </div>
                </li>
                <li class="flex gap-3 rounded-xl p-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40">
                        <x-icon name="star" size="4" />
                    </span>
                    <div>
                        <p class="text-sm font-medium">ثبت‌نام کاربر</p>
                        <p class="text-xs text-slate-500">مینا حسینی</p>
                    </div>
                </li>
                <li class="flex gap-3 rounded-xl p-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/40">
                        <x-icon name="products" size="4" />
                    </span>
                    <div>
                        <p class="text-sm font-medium">ارسال #۴۸۲۰</p>
                        <p class="text-xs text-slate-500">تیپاکس — تهران</p>
                    </div>
                </li>
            </ul>
            <a href="#" class="mt-4 flex items-center justify-center gap-1 text-sm font-medium text-primary-600 transition hover:text-primary-700">
                همه فعالیت‌ها
                <x-icon name="chevronLeft" size="4" />
            </a>
        </section>
    </div>

    <!-- Recent Orders Table -->
    <section class="chart-card mt-6 opacity-0-start observe-in overflow-hidden">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold">آخرین سفارشات</h3>
            </div>
            <a href="#" class="rounded-xl bg-gradient-to-l from-primary-500 to-violet-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-primary-500/30 transition hover:brightness-110">
                سفارش جدید
            </a>
        </div>
        <div class="overflow-x-auto -mx-5 px-5 sm:mx-0 sm:px-0">
            <table class="w-full min-w-[640px] text-right text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-500 dark:border-slate-700">
                        <th class="pb-3 font-semibold">شناسه</th>
                        <th class="pb-3 font-semibold">مشتری</th>
                        <th class="pb-3 font-semibold">محصول</th>
                        <th class="pb-3 font-semibold">مبلغ</th>
                        <th class="pb-3 font-semibold">وضعیت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                    <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="py-4 font-medium text-primary-600">#۴۸۲۱</td>
                        <td class="py-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold dark:bg-slate-700">م</span>
                                محمد رضایی
                            </div>
                        </td>
                        <td class="py-4 text-slate-600 dark:text-slate-300">لپ‌تاپ ایسوس</td>
                        <td class="py-4 font-medium">۴۵,۲۰۰,۰۰۰</td>
                        <td class="py-4"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">تحویل شده</span></td>
                    </tr>
                    <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="py-4 font-medium text-primary-600">#۴۸۲۰</td>
                        <td class="py-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold dark:bg-slate-700">س</span>
                                سارا احمدی
                            </div>
                        </td>
                        <td class="py-4">هدفون سونی</td>
                        <td class="py-4 font-medium">۳,۸۵۰,۰۰۰</td>
                        <td class="py-4"><span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">در حال ارسال</span></td>
                    </tr>
                    <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="py-4 font-medium text-primary-600">#۴۸۱۹</td>
                        <td class="py-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold dark:bg-slate-700">ع</span>
                                علی کریمی
                            </div>
                        </td>
                        <td class="py-4">کیبورد مکانیکال</td>
                        <td class="py-4 font-medium">۲,۱۰۰,۰۰۰</td>
                        <td class="py-4"><span class="rounded-full bg-primary-100 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">پرداخت شده</span></td>
                    </tr>
                    <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="py-4 font-medium text-primary-600">#۴۸۱۸</td>
                        <td class="py-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold dark:bg-slate-700">ن</span>
                                نازنین موسوی
                            </div>
                        </td>
                        <td class="py-4">مانیتور سامسونگ</td>
                        <td class="py-4 font-medium">۱۲,۵۰۰,۰۰۰</td>
                        <td class="py-4"><span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">لغو شده</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    function toPersianNum(n) {
        return String(n).replace(/\d/g, (d) => faDigits[+d]);
    }

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.2)';
    const tooltipBg = isDark ? '#1e293b' : '#ffffff';
    const tooltipBorder = isDark ? '#334155' : '#e2e8f0';

    Chart.defaults.font.family = 'Vazirmatn, Tahoma, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;

    // Revenue Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور'],
            datasets: [
                {
                    label: 'درآمد (میلیون تومان)',
                    data: [86, 94, 102, 118, 121, 128],
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
                {
                    label: 'هزینه',
                    data: [28, 35, 32, 40, 38, 45],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'transparent',
                    borderDash: [6, 4],
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0,
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

    // Category Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: ['پروژه‌ها', 'پشتیبانی', 'مشاوره', 'آموزش', 'سایر'],
            datasets: [{ data: [35, 25, 18, 14, 8], backgroundColor: ['#6366f1', '#8b5cf6', '#10b981', '#f59e0b', '#06b6d4'], borderWidth: 0, hoverOffset: 12 }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { position: 'bottom', rtl: true, labels: { color: textColor, padding: 14, usePointStyle: true } } },
            animation: { animateRotate: true, duration: 1400 },
        },
    });

    // Traffic Chart
    new Chart(document.getElementById('trafficChart'), {
        type: 'bar',
        data: {
            labels: ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'],
            datasets: [{
                label: 'بازدید',
                data: [1200, 1900, 1500, 2100, 1800, 2400, 1600],
                backgroundColor: (ctx) => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 280);
                    g.addColorStop(0, 'rgba(99, 102, 241, 0.9)');
                    g.addColorStop(1, 'rgba(139, 92, 246, 0.4)');
                    return g;
                },
                borderRadius: 10,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: { grid: { color: gridColor }, ticks: { color: textColor, callback: (v) => toPersianNum(v) } },
            },
            animation: { duration: 1200, delay: (ctx) => ctx.dataIndex * 80 },
        },
    });
});
</script>
@endpush