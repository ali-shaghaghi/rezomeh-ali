<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Services\RedirectService;

/**
 * تایید ایمیل — قابلیت اختیاری کاربر (پیش‌فرض غیرفعال).
 * فقط برای کاربرانی که «تایید ایمیل» را در تنظیمات امنیتی فعال کرده‌اند.
 */
class EmailVerificationController extends Controller
{
    /**
     * صفحه اعلان تایید ایمیل (بعد از ورود، وقتی ایمیل تایید نشده است).
     */
    public function notice(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('auth.login');
        }

        // اگر ایمیل تایید شده، مستقیم به مقصد نقش‌محور
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RedirectService::target($user));
        }

        return view('auth::auth.email-verify', [
            'maskedEmail' => app(\Modules\Core\Services\OtpService::class)->maskEmail($user->email),
        ]);
    }

    /**
     * تایید با لینک امضاشده ارسال‌شده به ایمیل.
     * لینک بدون نیاز به سشن کار می‌کند (امضای URL + هش ایمیل).
     */
    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        if (Auth::check()) {
            return redirect()->intended(RedirectService::target($user))
                ->with('success', 'ایمیل شما با موفقیت تایید شد.');
        }

        return redirect()->route('auth.login')
            ->with('success', 'ایمیل شما با موفقیت تایید شد. برای ادامه وارد شوید.');
    }

    /**
     * ارسال مجدد لینک تایید.
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('auth.login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RedirectService::target($user));
        }

        $user->sendEmailVerificationNotification();

        return back()->with('verification-link-sent', true);
    }
}
