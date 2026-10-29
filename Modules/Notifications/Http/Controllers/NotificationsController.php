<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;

class NotificationsController extends Controller
{
    /**
     * صفحه کامل اعلان‌های ادمین.
     */
    public function index()
    {
        abort_unless(auth()->user()?->hasPermission('view_notifications'), 404);

        return view('notifications::admin.index');
    }

    /**
     * صفحه اعلان‌های پنل کاربر.
     */
    public function userIndex()
    {
        abort_unless(auth()->check(), 404);

        return view('notifications::user.index');
    }
}
