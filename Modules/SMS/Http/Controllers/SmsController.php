<?php

namespace Modules\SMS\Http\Controllers;

use App\Http\Controllers\Controller;

class SmsController extends Controller
{
    /**
     * دسترسی فقط با مجوز مدیریت پیامک — قانون پروژه: غیرمجاز = 404
     */
    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasPermission('manage_sms'), 404);
    }

    /**
     * تنظیمات SMS
     */
    public function index()
    {
        $this->authorizeAccess();

        return view('sms::admin.settings');
    }

    /**
     * لاگ پیامک‌های ارسالی
     */
    public function logs()
    {
        $this->authorizeAccess();

        return view('sms::admin.logs');
    }

    /**
     * مدیریت قالب‌ها
     */
    public function templates()
    {
        $this->authorizeAccess();

        return view('sms::admin.templates');
    }
}