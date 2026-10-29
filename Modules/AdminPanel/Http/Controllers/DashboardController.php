<?php

namespace Modules\AdminPanel\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\Core\Models\BlogPost;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Tickets\Models\Ticket;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Statistics
        $stats = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'open_tickets' => Ticket::where('status', 'open')->count(),
            'total_posts' => BlogPost::count(),
            'total_views' => BlogPost::sum('views'),
        ];

        // Monthly revenue for chart (last 6 months)
        // استخراج ماه به‌صورت سازگار با موتور دیتابیس: MONTH() در MySQL،
        // strftime در SQLite. strftime در MySQL وجود ندارد (root cause خطای قبلی).
        $driver = Payment::query()->getConnection()->getDriverName();
        $monthExpr = match ($driver) {
            'sqlite' => "CAST(strftime('%m', paid_at) AS INTEGER)",
            default  => 'MONTH(paid_at)',
        };

        $monthlyRevenue = Payment::where('status', 'completed')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // برچسب و داده نمودار درآمد — ماه‌های خالی با ۰ (داینامیک)
        $faMonths = [1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان', 9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'];
        $monthlyRevenueLabels = [];
        $monthlyRevenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $gregorianMonth = (int) $date->format('n');
            // نگاشت ساده ماه میلادی به برچسب نمایشی
            $monthlyRevenueLabels[] = $faMonths[$gregorianMonth];
            $monthlyRevenueData[] = (int) round(($monthlyRevenue[$gregorianMonth] ?? 0) / 1000000, 1);
        }

        // Recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Recent payments
        $recentPayments = Payment::with('user')
            ->latest()
            ->take(5)
            ->get();

        // فعالیت‌های اخیر — از سیستم ردیابی واقعی کاربران (داینامیک)
        $recentActivity = app(\Modules\Analytics\Services\AnalyticsService::class)->recentActivity(8);

        // ترافیک هفتگی — بازدید صفحات در ۷ روز گذشته (داینامیک)
        $weeklyTraffic = \Modules\Core\Models\UserActivity::query()
            ->where('last_active_at', '>=', now()->subDays(7))
            ->get()
            ->groupBy(fn ($a) => $a->last_active_at->format('Y-m-d'))
            ->map(fn ($day) => $day->count());

        $weeklyTrafficLabels = [];
        $weeklyTrafficData = [];
        $faDays = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyTrafficLabels[] = $faDays[$date->dayOfWeek].' ('.$date->format('m/d').')';
            $weeklyTrafficData[] = $weeklyTrafficLabels !== [] ? (int) ($weeklyTraffic[$date->format('Y-m-d')] ?? 0) : 0;
        }
        // بازنویسی صحیح داده‌ها (ترتیب حلقه بالا با labels همگام است)
        $weeklyTrafficData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyTrafficData[] = (int) ($weeklyTraffic[$date->format('Y-m-d')] ?? 0);
        }

        return view('adminpanel::dashboard.index', compact(
            'stats',
            'monthlyRevenue',
            'monthlyRevenueLabels',
            'monthlyRevenueData',
            'recentOrders',
            'recentPayments',
            'recentActivity',
            'weeklyTrafficLabels',
            'weeklyTrafficData'
        ));
    }
}
