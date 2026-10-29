<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Services\RedirectService;

class SocialiteController extends Controller
{
    /**
     * Redirect to Google/GitHub for authentication.
     *
     * اگر کلاینت OAuth در services/env تنظیم نشده باشد، کاربر با پیام واضح
     * به صفحه ورود برمی‌گردد (به‌جای خطای ۵۰۰).
     */
    public function redirect(Request $request, string $provider)
    {
        if (! $this->providerIsConfigured($provider)) {
            return redirect()
                ->route('auth.login')
                ->withErrors(['credential' => $this->notConfiguredMessage($provider)]);
        }

        try {
            // redirect_uri همیشه از روت واقعی اپلیکیشن ساخته می‌شود تا با
            // آدرس ثبت‌شده در Google Console / GitHub همیشه سازگار باشد.
            return Socialite::driver($provider)
                ->redirectUrl(route('auth.socialite.callback', $provider))
                ->redirect();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Socialite [{$provider}] redirect error: ".$e->getMessage());

            return redirect()
                ->route('auth.login')
                ->withErrors(['credential' => $this->genericFailureMessage($provider)]);
        }
    }

    /**
     * Handle the callback from Google/GitHub.
     */
    public function callback(Request $request, string $provider)
    {
        if (! $this->providerIsConfigured($provider)) {
            return redirect()
                ->route('auth.login')
                ->withErrors(['credential' => $this->notConfiguredMessage($provider)]);
        }

        try {
            // همان redirect_uri که در مرحله redirect استفاده شده (الزام OAuth)
            $socialUser = Socialite::driver($provider)
                ->redirectUrl(route('auth.socialite.callback', $provider))
                ->user();

            // ۱) ابتدا با شناسه اجتماعی (provider + social_id) جستجو می‌شود
            $user = User::where('social_provider', $provider)
                ->where('social_id', (string) $socialUser->getId())
                ->first();

            // ۲) اگر یافت نشد، با ایمیل (حساب قدیمی که با فرم ثبت‌نام ساخته شده)
            if (! $user && $socialUser->getEmail()) {
                $user = User::where('email', $socialUser->getEmail())->first();
            }

            // ۳) در نهایت کاربر جدید ساخته می‌شود
            if (! $user) {
                $user = User::create([
                    'name' => $socialUser->getName() ?: $socialUser->getNickname(),
                    'email' => $socialUser->getEmail(),
                    // ستون password در دیتابیس NOT NULL است؛ رمز تصادفی تولید می‌شود
                    // (کاربر می‌تواند از طریق «فراموشی رمز عبور» آن را بازیابی کند)
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                    'avatar' => $this->getAvatar($socialUser, $provider),
                    'social_provider' => $provider,
                    'social_id' => (string) $socialUser->getId(),
                    'is_active' => true,
                ]);

                // نقش پیش‌فرض «کاربر عادی» (در صورت seed شدن)
                if ($userRole = \Modules\Core\Models\Role::where('slug', 'user')->first()) {
                    $user->roles()->attach($userRole);
                }

                // اعلان «ثبت‌نام کاربر جدید» به ادمین‌ها
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
            } else {
                // تکمیل/به‌روزرسانی اطلاعات اجتماعی حساب موجود
                $user->forceFill([
                    'social_provider' => $user->social_provider ?? $provider,
                    'social_id' => (string) $socialUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }

            if (! $user->is_active) {
                return redirect()
                    ->route('auth.login')
                    ->withErrors(['credential' => 'حساب کاربری شما غیرفعال است.']);
            }

            // ورود کاربر (ادمین یا عادی — مقصد نهایی بر اساس نقش)
            Auth::login($user, true);
            $request->session()->regenerate();

            // ریدایرکت نقش‌محور: ادمین → داشبورد ادمین، کاربر عادی → داشبورد کاربر
            return redirect()->intended(RedirectService::target($user));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Socialite [{$provider}] callback error: ".$e->getMessage());

            return redirect()
                ->route('auth.login')
                ->withErrors(['credential' => $this->genericFailureMessage($provider)]);
        }
    }

    /**
     * آیا درایور OAuth موردنظر در services/env تنظیم شده است؟
     */
    protected function providerIsConfigured(string $provider): bool
    {
        $config = config("services.{$provider}");

        return is_array($config)
            && ! empty($config['client_id'])
            && ! empty($config['client_secret'])
            && str_starts_with((string) $config['client_id'], 'your_') === false;
    }

    protected function notConfiguredMessage(string $provider): string
    {
        return match ($provider) {
            'google' => 'ورود با گوگل هنوز پیکربندی نشده است. GOOGLE_CLIENT_ID و GOOGLE_CLIENT_SECRET را در تنظیمات وارد کنید.',
            'github' => 'ورود با گیت‌هاب هنوز پیکربندی نشده است. GITHUB_CLIENT_ID و GITHUB_CLIENT_SECRET را در تنظیمات وارد کنید.',
            default => 'ورود با این سرویس پیکربندی نشده است.',
        };
    }

    protected function genericFailureMessage(string $provider): string
    {
        return match ($provider) {
            'google' => 'خطایی در ورود با گوگل رخ داد. لطفاً دوباره تلاش کنید.',
            'github' => 'خطایی در ورود با گیت‌هاب رخ داد. لطفاً دوباره تلاش کنید.',
            default => 'خطایی در ورود اجتماعی رخ داد. لطفاً دوباره تلاش کنید.',
        };
    }

    /**
     * Get avatar from social provider.
     */
    protected function getAvatar($socialUser, string $provider): ?string
    {
        try {
            $avatarUrl = $socialUser->getAvatar();
            if ($avatarUrl) {
                // Download and store avatar
                $avatarContent = file_get_contents($avatarUrl);
                $extension = pathinfo(parse_url($avatarUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = time() . '_' . $provider . '.' . $extension;
                \Illuminate\Support\Facades\Storage::disk('public')->put('avatars/' . $filename, $avatarContent);
                return $filename;
            }
        } catch (\Exception $e) {
            // Fallback to default avatar
        }
        return null;
    }
}