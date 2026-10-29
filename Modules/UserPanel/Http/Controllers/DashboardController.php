<?php

namespace Modules\UserPanel\Http\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * نگاشت تب‌های قدیمی (?tab=X و URLهای جدا) به تب شل ریل‌تایم — سازگاری لینک‌های قدیمی
     */
    private const LEGACY_TAB_ROUTES = [
        'overview' => 'dashboard',
        'orders' => 'orders',
        'tickets' => 'tickets',
        'projects' => 'projects',
        'security' => 'security',
    ];

    /**
     * پنل کاربر — شل ریل‌تایم Livewire؛ سوییچ بین بخش‌ها بدون رفرش انجام می‌شود
     * و هر بخش کامپوننت Livewire خودش را از ماژول خودش رندر می‌کند.
     * سازگاری: /dashboard?tab=X → تب متناظر در همان صفحه (بدون ریدایرکت).
     */
    public function index()
    {
        $tab = (string) request('tab', 'dashboard');

        $tab = self::LEGACY_TAB_ROUTES[$tab] ?? 'dashboard';

        return view('userpanel::dashboard.index', ['initialTab' => $tab]);
    }

    /**
     * فرم تیکت جدید — Livewire مستقل (از لیست تیکت‌ها با wire:navigate)
     */
    public function ticketCreate()
    {
        return view('userpanel::tickets.create');
    }
}
