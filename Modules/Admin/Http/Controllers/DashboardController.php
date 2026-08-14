<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\Core\Models\Order;
use Modules\Core\Models\Payment;
use Modules\Core\Models\Ticket;
use Modules\Core\Models\BlogPost;

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
        $monthlyRevenue = Payment::where('status', 'completed')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->selectRaw('MONTH(paid_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

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

        // Recent users
        $recentUsers = User::latest()
            ->take(5)
            ->get();

        return view('admin::dashboard.index', compact(
            'stats',
            'monthlyRevenue',
            'recentOrders',
            'recentPayments',
            'recentUsers'
        ));
    }
}