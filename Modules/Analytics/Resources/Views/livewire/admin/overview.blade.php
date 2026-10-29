<div>
    {{-- کارت‌های آمار کلی --}}
    <div class="mb-8 grid gap-4 grid-cols-2 xl:grid-cols-4">
        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">درآمد کل</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                    <x-icon name="wallet" size="5" />
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ fa_digits(number_format($stats['total_revenue'] / 1000000, 1)) }}<span class="text-sm font-medium text-slate-500"> میلیون</span></p>
            <p class="mt-1 text-[11px] text-slate-400">این ماه: {{ fa_digits(number_format($stats['revenue_this_month'] / 1000000, 1)) }} م</p>
        </div>

        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">سفارشات</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">
                    <x-icon name="orders" size="5" />
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ fa_digits($stats['total_orders']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">تکمیل‌شده: {{ fa_digits($stats['completed_orders']) }}</p>
        </div>

        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">کاربران</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-400">
                    <x-icon name="users" size="5" />
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ fa_digits($stats['total_users']) }}</p>
            <p class="mt-1 text-[11px] text-emerald-500">آنلاین: {{ fa_digits($stats['online_users']) }}</p>
        </div>

        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">تیکت‌های باز</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                    <x-icon name="bell" size="5" />
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ fa_digits($stats['open_tickets']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">مقالات: {{ fa_digits($stats['total_posts']) }} · بازدید: {{ fa_digits($stats['total_views']) }}</p>
        </div>
    </div>

    {{-- نمودارهای روند --}}
    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <section class="chart-card lg:col-span-2">
            <h3 class="mb-4 text-lg font-bold">روند درآمد (۶ ماه)</h3>
            <div class="h-64 sm:h-72">
                <canvas id="revenueChart" aria-label="نمودار روند درآمد"></canvas>
            </div>
        </section>

        <section class="chart-card">
            <h3 class="mb-4 text-lg font-bold">وضعیت سفارشات</h3>
            <div class="mx-auto h-56 max-w-xs sm:h-64">
                <canvas id="orderStatusChart" aria-label="نمودار وضعیت سفارشات"></canvas>
            </div>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="chart-card">
            <h3 class="mb-4 text-lg font-bold">سفارشات ماهانه</h3>
            <div class="h-56 sm:h-64">
                <canvas id="ordersChart" aria-label="نمودار سفارشات ماهانه"></canvas>
            </div>
        </section>

        <section class="chart-card">
            <h3 class="mb-4 text-lg font-bold">کاربران جدید</h3>
            <div class="h-56 sm:h-64">
                <canvas id="usersChart" aria-label="نمودار کاربران جدید"></canvas>
            </div>
        </section>

        <section class="chart-card">
            <h3 class="mb-4 text-lg font-bold">روش‌های پرداخت</h3>
            <div class="mx-auto h-56 max-w-xs sm:h-64">
                <canvas id="paymentMethodChart" aria-label="نمودار روش‌های پرداخت"></canvas>
            </div>
        </section>
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
    const tooltipBg = isDark ? '#1e293b' : '#ffffff';
    const tooltipBorder = isDark ? '#334155' : '#e2e8f0';

    Chart.defaults.font.family = 'Vazirmatn, Tahoma, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;

    const baseLineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', align: 'end', rtl: true, labels: { color: textColor, usePointStyle: true, padding: 16 } },
            tooltip: { rtl: true, backgroundColor: tooltipBg, titleColor: textColor, bodyColor: textColor, borderColor: tooltipBorder, borderWidth: 1, padding: 12, cornerRadius: 12 },
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: textColor } },
            y: { grid: { color: gridColor }, ticks: { color: textColor, callback: (v) => toPersianNum(v) } },
        },
        animation: { duration: 1500, easing: 'easeOutQuart' },
    };

    function makeLineChart(canvasId, labels, data, label, color) {
        new Chart(document.getElementById(canvasId), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: color,
                    backgroundColor: color.replace(')', ', 0.12)').replace('rgb', 'rgba'),
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: isDark ? '#1e293b' : '#fff',
                    pointBorderColor: color,
                    pointBorderWidth: 2,
                }],
            },
            options: baseLineOptions,
        });
    }

    makeLineChart('revenueChart', @json($revenueChart['labels']), @json($revenueChart['data']), 'درآمد (تومان)', 'rgb(99, 102, 241)');
    makeLineChart('ordersChart', @json($ordersChart['labels']), @json($ordersChart['data']), 'سفارشات', 'rgb(139, 92, 246)');
    makeLineChart('usersChart', @json($usersChart['labels']), @json($usersChart['data']), 'کاربران جدید', 'rgb(6, 182, 212)');

    new Chart(document.getElementById('orderStatusChart'), {
        type: 'doughnut',
        data: {
            labels: @json($orderStatusChart['labels']),
            datasets: [{
                data: @json($orderStatusChart['data']),
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

    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'doughnut',
        data: {
            labels: @json($paymentMethodChart['labels']),
            datasets: [{
                data: @json($paymentMethodChart['data']),
                backgroundColor: ['#6366f1', '#f59e0b', '#10b981'],
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
