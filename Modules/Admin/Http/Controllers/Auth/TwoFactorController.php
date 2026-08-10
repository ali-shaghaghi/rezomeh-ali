<?php

namespace Modules\Admin\Http\Controllers\Auth;

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
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('admin.login');
        }

        $maskedPhone = $user->phone
            ? substr($user->phone, 0, 3) . str_repeat('*', strlen($user->phone) - 5) . substr($user->phone, -2)
            : '***';

        return view('admin::auth.two-factor', compact('maskedPhone'));
    }

    /**
     * Handle two-factor verification.
     */
    public function verify(Request $request)
    {
        // This is handled by the Livewire TwoFactorForm component
        return redirect()->route('admin.two-factor.verify');
    }
}