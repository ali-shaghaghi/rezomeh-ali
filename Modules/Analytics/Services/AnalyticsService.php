<?php

namespace Modules\Analytics\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Consultation\Models\Consultation;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\UserActivity;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Products\Models\OrderItem;
use Modules\Products\Models\Product;
use Modules\Tickets\Models\Ticket;

/**
 * سرویس مرکزی گزارش‌گیری ماژول Analytics.
 *
 * تمام کوئری‌های آماری پنل ادمین اینجا متمرکز شده‌اند تا هم در داشبورد
 * اصلی و هم در صفحات گزارش قابل استفاده باشند. تمام عبارات تاریخ
 * به‌صورت سازگار با MySQL و SQLite (محیط تست) نوشته شده‌اند.
 */
class AnalyticsService
{
    /**
     * نام ماه‌های شمسی برای برچسب نمودارها.
     */
    public const JALALI_MONTHS = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
        5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
        9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];

    /**
     * برچسب روش‌های پرداخت (مشترک با PaymentService).
     */
    public const PAYMENT_METHODS = [
        'gateway' => 'درگاه آنلاین',
        'installment' => 'اقساطی',
        'manual' => 'دستی',
    ];

    /**
     * برچسب وضعیت‌های سفارش.
     */
    public const ORDER_STATUSES = [
        'pending' => 'در انتظار بررسی',
        'review' => 'در حال بررسی',
        'accepted' => 'پذیرفته شده',
        'development' => 'در حال توسعه',
        'completed' => 'تکمیل شده',
        'cancelled' => 'لغو شده',
    ];

    // ======================================================================
    // آمار کلی (Overview)
    // ======================================================================

    /**
     * کارت‌های آمار سریع داشبورد گزارش‌ها.
     */
    public function keyStats(): array
    {
        return [
            'total_revenue' => (float) Payment::completed()->sum('amount'),
            'revenue_this_month' => (float) Payment::completed()
                ->where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
            'total_orders' => Order::count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_users' => User::count(),
            'users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'online_users' => UserActivity::where('last_active_at', '>=', now()->subMinutes(5))
                ->distinct('user_id')->count('user_id'),
            'open_tickets' => Ticket::open()->count(),
            'total_posts' => BlogPost::count(),
            'total_views' => (int) BlogPost::sum('views'),
        ];
    }

    // ======================================================================
    // روند ماهانه (نمودارهای خطی)
    // ======================================================================

    /**
     * روند درآمد ماهانه برای N ماه گذشته.
     *
     * @return array{labels: array<int, string>, data: array<int, float>}
     */
    public function monthlyRevenue(int $months = 6): array
    {
        $raw = $this->monthlyAggregation(
            Payment::completed()->where('paid_at', '>=', now()->subMonths($months)),
            'paid_at',
            'amount',
            $months
        );

        return $this->toMonthChart($raw);
    }

    /**
     * تعداد سفارشات ثبت‌شده در N ماه گذشته.
     */
    public function monthlyOrders(int $months = 6): array
    {
        $raw = $this->monthlyAggregation(
            Order::where('created_at', '>=', now()->subMonths($months)),
            'created_at',
            'id',
            $months,
            'count'
        );

        return $this->toMonthChart($raw);
    }

    /**
     * کاربران جدید در N ماه گذشته.
     */
    public function monthlyUsers(int $months = 6): array
    {
        $raw = $this->monthlyAggregation(
            User::where('created_at', '>=', now()->subMonths($months)),
            'created_at',
            'id',
            $months,
            'count'
        );

        return $this->toMonthChart($raw);
    }

    /**
     * تبدیل آرایه «شماره ماه => مقدار» به ساختار نمودار (labels/data).
     */
    protected function toMonthChart(array $raw): array
    {
        return [
            'labels' => array_map(fn ($m) => self::JALALI_MONTHS[$m] ?? (string) $m, array_keys($raw)),
            'data' => array_values($raw),
        ];
    }

    // ======================================================================
    // نمودارهای دونات/دایره‌ای
    // ======================================================================

    /**
     * تقسیم سفارشات بر اساس وضعیت.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function ordersByStatus(): array
    {
        $counts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // فقط وضعیت‌هایی که حداقل یک رکورد دارند
        $labels = array_filter(
            self::ORDER_STATUSES,
            fn ($key) => $counts->has($key),
            ARRAY_FILTER_USE_KEY
        );

        return [
            'labels' => array_values($labels),
            'data' => array_map(fn ($key) => (int) $counts->get($key, 0), array_keys($labels)),
        ];
    }

    /**
     * تقسیم پرداخت‌های موفق بر اساس روش پرداخت.
     *
     * @return array{labels: array<int, string>, data: array<int, int>, amounts: array<int, float>}
     */
    public function paymentsByMethod(): array
    {
        $rows = Payment::completed()
            ->selectRaw('payment_method, COUNT(*) as total, SUM(amount) as amount')
            ->groupBy('payment_method')
            ->get();

        $labels = $data = $amounts = [];

        foreach (self::PAYMENT_METHODS as $key => $label) {
            $row = $rows->firstWhere('payment_method', $key);
            $labels[] = $label;
            $data[] = (int) ($row?->total ?? 0);
            $amounts[] = (float) ($row?->amount ?? 0);
        }

        return ['labels' => $labels, 'data' => $data, 'amounts' => $amounts];
    }

    // ======================================================================
    // گزارش فروش
    // ======================================================================

    /**
     * پرداخت‌های تکمیل‌شده با فیلتر بازه زمانی و روش پرداخت.
     *
     * @return Collection<int, Payment>
     */
    public function payments(?string $from = null, ?string $to = null, ?string $method = null): Collection
    {
        $query = Payment::completed()->with(['user', 'order']);

        if ($from) {
            $query->where('paid_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->where('paid_at', '<=', Carbon::parse($to)->endOfDay());
        }

        if ($method && isset(self::PAYMENT_METHODS[$method])) {
            $query->where('payment_method', $method);
        }

        return $query->latest('paid_at')->get();
    }

    /**
     * پرفروش‌ترین محصولات بر اساس تعداد فروش.
     *
     * @return Collection<int, object{name: string, quantity: int, revenue: float}>
     */
    public function topProducts(int $limit = 10): Collection
    {
        return OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as quantity, SUM(total_price) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get();
    }

    // ======================================================================
    // گزارش ترافیک کاربران
    // ======================================================================

    /**
     * کاربران آنلاین (فعال در N دقیقه گذشته) — یونیک بر اساس کاربر.
     */
    public function onlineUsers(int $minutes = 5): Collection
    {
        return UserActivity::where('last_active_at', '>=', now()->subMinutes($minutes))
            ->with('user')
            ->latest('last_active_at')
            ->get()
            ->unique('user_id');
    }

    /**
     * آخرین فعالیت‌های ثبت‌شده در سیستم.
     */
    public function recentActivity(int $limit = 15): Collection
    {
        return UserActivity::with('user')
            ->latest('last_active_at')
            ->limit($limit)
            ->get();
    }

    /**
     * صفحات پر بازدید بر اساس رکوردهای فعالیت کاربران.
     */
    public function topPages(int $limit = 10): Collection
    {
        return UserActivity::selectRaw('page, COUNT(*) as visits')
            ->groupBy('page')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();
    }

    // ======================================================================
    // گزارش محتوا
    // ======================================================================

    /**
     * مقالات پربازدید.
     */
    public function topPosts(int $limit = 10): Collection
    {
        return BlogPost::orderByDesc('views')->limit($limit)->get();
    }

    /**
     * آمار فروشگاه محصولات.
     */
    public function productStats(): array
    {
        return [
            'total' => Product::count(),
            'published' => Product::published()->count(),
            'total_sales' => (int) OrderItem::sum('quantity'),
            'sales_revenue' => (float) OrderItem::sum('total_price'),
            'licenses' => \Modules\Products\Models\License::count(),
            'downloads' => \Modules\Products\Models\Download::count(),
        ];
    }

    /**
     * آمار مشاوره‌ها.
     */
    public function consultationStats(): array
    {
        return [
            'total' => Consultation::count(),
            'completed' => Consultation::where('status', 'completed')->count(),
            'cancelled' => Consultation::where('status', 'cancelled')->count(),
            // درآمد مشاوره = مبلغ پرداخت‌شده سفارش متصل به جلسات تکمیل‌شده
            'revenue' => (float) Consultation::query()
                ->where('consultations.status', 'completed')
                ->join('orders', 'orders.id', '=', 'consultations.order_id')
                ->sum('orders.paid_amount'),
        ];
    }

    // ======================================================================
    // ابزار داخلی
    // ======================================================================

    /**
     * تجمیع ماهانه برای هر کوئری — سازگار با MySQL و SQLite.
     *
     * ماه‌های خالی را با ۰ پر می‌کند تا نمودار خطی پیوسته بماند.
     *
     * @param  'sum'|'count'  $aggregate
     * @return array<int, float|int> کلید = شماره ماه جلالی (۱..۱۲)
     */
    protected function monthlyAggregation(object $query, string $dateColumn, string $valueColumn, int $months, string $aggregate = 'sum'): array
    {
        $driver = $query->getQuery()->getConnection()->getDriverName();
        $monthExpr = match ($driver) {
            'sqlite' => "CAST(strftime('%m', {$dateColumn}) AS INTEGER)",
            default => "MONTH({$dateColumn})",
        };

        $valueExpr = $aggregate === 'count'
            ? "COUNT({$valueColumn}) as total"
            : "SUM({$valueColumn}) as total";

        $rows = $query
            ->selectRaw("{$monthExpr} as month, {$valueExpr}")
            ->groupBy('month')
            ->pluck('total', 'month');

        $result = [];
        $start = now()->subMonths($months - 1);

        for ($i = 0; $i < $months; $i++) {
            $month = (int) $start->copy()->addMonths($i)->format('n');
            $value = $rows->get($month);
            $result[$month] = $aggregate === 'count' ? (int) $value : (float) $value;
        }

        return $result;
    }
}
