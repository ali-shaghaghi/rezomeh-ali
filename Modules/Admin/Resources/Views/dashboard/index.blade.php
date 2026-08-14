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
            <h3 class="mb-4 text-lg font-bold">وضعیت سفارشات</h3>
            <div class="mx-auto h-56 max-w-xs sm:h-64">
                <canvas id="orderStatusChart" aria-label="نمودار وضعیت سفارشات"></canvas>
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <!-- Recent Activity -->
        <section class="chart-card opacity-0-start observe-in xl:col-span-2">
            <h3 class="mb-4 text-lg font-bold">آخرین سفارشات</h3>
            <div class="overflow-x-auto -mx-5 px-5 sm:mx-0 sm:px-0">
                <table class="w-full min-w-[640px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-500 dark:border-slate-700">
                            <th class="pb-3 font-semibold">شناسه</th>
                            <th class="pb-3 font-semibold">مشتری</th>
                            <th class="pb-3 font-semibold">عنوان</th>
                            <th class="pb-3 font-semibold">مبلغ</th>
                            <th class="pb-3 font-semibold">وضعیت</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                        @forelse($recentOrders as $order)
                            <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                                <td class="py-4 font-medium text-primary-600">#{{ $order->order_number }}</td>
                                <td class="py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold dark:bg-slate-700">{{ mb_substr($order->user->name, 0, 1) }}</span>
                                        {{ $order->user->name }}
                                    </div>
                                </td>
                                <td class="py-4 text-slate-600 dark:text-slate-300">{{ $order->title }}</td>
                                <td class="py-4 font-medium">{{ number_format($order->amount) }}</td>
                                <td class="py-4">
                                    <span class="rounded-full bg-{{ $order->status_color }}-100 px-2.5 py-1 text-xs font-medium text-{{ $order->status_color }}-700 dark:bg-{{ $order->status_color }}-900/40 dark:text-{{ $order->status_color }}-300">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">سفارشی یافت نشد</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Recent Users -->
        <section class="chart-card opacity-0-start observe-in">
            <h3 class="mb-4 text-lg font-bold">آخرین کاربران</h3>
            <ul class="space-y-4">
                @forelse($recentUsers as $user)
                    <li class="flex gap-3 rounded-xl p-2 transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-xs font-bold dark:bg-slate-700">
                            {{ mb_substr($user->name, 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                        <span class="text-xs text-slate-400">{{ $user->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-sm text-slate-500">کاربری یافت نشد</li>
                @endforelse
            </ul>
        </section>
    </div>
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
});
</script>
@endpush