<?php

namespace Modules\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function showForm()
    {
        return view('admin::auth.password-reset');
    }

    /**
     * Handle the password reset request.
     */
    public function reset(Request $request)
    {
        // This is handled by the Livewire PasswordResetForm component
        return redirect()->route('admin.password.request');
    }
}