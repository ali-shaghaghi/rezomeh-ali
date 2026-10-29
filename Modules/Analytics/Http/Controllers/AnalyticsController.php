<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;

class AnalyticsController extends Controller
{
    /**
     * دسترسی فقط با مجوز مشاهده گزارش‌ها — قانون پروژه: غیرمجاز = 404
     */
    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasPermission('view_reports'), 404);
    }

    /**
     * داشبورد گزارش‌ها — نمودارهای روند + آمار کلی.
     */
    public function overview()
    {
        $this->authorizeAccess();

        return view('analytics::overview');
    }

    /**
     * گزارش فروش — پرداخت‌های موفق + محصولات پرفروش.
     */
    public function sales()
    {
        $this->authorizeAccess();

        return view('analytics::sales');
    }

    /**
     * گزارش ترافیک — کاربران آنلاین، فعالیت‌ها و صفحات پر بازدید.
     */
    public function traffic()
    {
        $this->authorizeAccess();

        return view('analytics::traffic');
    }

    /**
     * گزارش محتوا — مقالات، محصولات و مشاوره‌ها.
     */
    public function content()
    {
        $this->authorizeAccess();

        return view('analytics::content');
    }
}
