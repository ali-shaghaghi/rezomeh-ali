<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\RedirectService;
use Modules\Core\Models\Role;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('auth::auth.register');
    }

    /**
     * Handle a registration request.
     *
     * از جدول فعلی users استفاده می‌شود (بدون migration جدید).
     * نقش پیش‌فرض «user» (جدول user_role) و سپس ریدایرکت نقش‌محور (خواسته ۲.۲).
     */
    public function register(RegisterRequest $request)
    {
        // فیلد password با cast «hashed» مدل User به‌صورت خودکار هش می‌شود
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_active' => true,
        ]);

        // اتصال نقش پیش‌فرض «کاربر عادی» (در صورت seed شدن)
        if ($userRole = Role::where('slug', 'user')->first()) {
            $user->roles()->attach($userRole);
        }

        // اعلان «ثبت‌نام کاربر جدید» به ادمین‌ها (خطا نباید ثبت‌نام را بشکند)
        try {
            app(\Modules\Notifications\Services\NotificationService::class)
                ->notifyUsersWithPermission(
                    'view_notifications',
                    \Modules\Notifications\Notifications\NewUserNotification::class,
                    [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email ?? '',
                    ]
                );
        } catch (\Throwable $e) {
            report($e);
        }

        // ورود خودکار + Session Regeneration
        Auth::login($user);
        $request->session()->regenerate();

        // ریدایرکت نقش‌محور: ادمین → داشبورد ادمین، کاربر عادی → داشبورد کاربر
        return redirect()->intended(RedirectService::target($user))
            ->with('success', 'حساب کاربری شما با موفقیت ایجاد شد. خوش آمدید!');
    }
}
