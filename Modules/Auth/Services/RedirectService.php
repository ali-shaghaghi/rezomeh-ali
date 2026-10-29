<?php

namespace Modules\Auth\Services;

use App\Models\User;

class RedirectService
{
    /**
     * آدرس مقصد پس از ورود موفق کاربران.
     *
     * کاربران جدول users هرگز ارتقا سطح پیدا نمی‌کنند و فقط به پنل کاربر می‌روند؛
     * ادمین‌ها از مسیر جداگانه /admin/login وارد پنل ادمین می‌شوند.
     */
    public static function target(User $user): string
    {
        return route('user.dashboard');
    }
}
