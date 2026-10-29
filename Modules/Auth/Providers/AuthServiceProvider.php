<?php

namespace Modules\Auth\Providers;

use Livewire\Livewire;
use Modules\Auth\Livewire\ChangePassword;
use Modules\Auth\Livewire\LoginForm;
use Modules\Auth\Livewire\OtpVerification;
use Modules\Auth\Livewire\PasswordResetForm;
use Modules\Auth\Livewire\TwoFactorForm;
use Modules\Core\Providers\BaseServiceProvider;

class AuthServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Auth';
    }

    public function boot(): void
    {
        parent::boot();

        // روتینگ یکپارچه احراز هویت (ورود/ثبت‌نام/OTP/2FA/بازیابی رمز/خروج)
        $this->loadRoutesFrom(module_route_path('Auth', 'web.php'));

        // روتینگ ورود/خروج ادمین‌ها — گارد و جدول جدا از کاربران
        $this->loadRoutesFrom(module_route_path('Auth', 'admin.php'));

        // ثبت کامپوننت‌های Livewire ماژول Auth
        Livewire::component('auth.login-form', LoginForm::class);
        Livewire::component('auth.otp-verification', OtpVerification::class);
        Livewire::component('auth.password-reset-form', PasswordResetForm::class);
        Livewire::component('auth.two-factor-form', TwoFactorForm::class);
        Livewire::component('auth.change-password', ChangePassword::class);

        // پنل کاربر — تنظیمات حساب (شل ریل‌تایم پنل، این کامپوننت را از ماژول Auth فراخوانی می‌کند)
        Livewire::component('auth.account-security', \Modules\Auth\Livewire\UserPanel\AccountSecurity::class);
    }
}
