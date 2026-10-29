<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class TwoFactorController extends Controller
{
    /**
     * Show the two-factor verification form.
     */
    public function showForm()
    {
        $userId = session('two_factor_user_id');

        if (!$userId) {
            return redirect()->route('auth.login');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('auth.login');
        }

        // کد دومرحله‌ای از طریق ایمیل ارسال می‌شود
        $maskedContact = $user->email
            ? app(\Modules\Core\Services\OtpService::class)->maskEmail($user->email)
            : '***';

        return view('auth::auth.two-factor', compact('maskedContact'));
    }

    /**
     * Handle two-factor verification.
     */
    public function verify(Request $request)
    {
        // This is handled by the Livewire TwoFactorForm component
        return redirect()->route('auth.two-factor.verify');
    }
}