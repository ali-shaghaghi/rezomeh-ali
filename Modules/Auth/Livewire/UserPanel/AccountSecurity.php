<?php

namespace Modules\Auth\Livewire\UserPanel;

use Livewire\Component;

/**
 * تنظیمات حساب پنل کاربر — پروفایل/رمز عبور/امنیت پیشرفته
 * (کامپوننت‌های مشترک Settings را از ماژول خودشان فراخوانی می‌کند).
 */
class AccountSecurity extends Component
{
    public function render()
    {
        return view('auth::livewire.user-panel.account-security');
    }
}
