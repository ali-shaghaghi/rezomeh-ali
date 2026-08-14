<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\DashboardController;
use Modules\Admin\Http\Controllers\Auth\LoginController;
use Modules\Admin\Http\Controllers\Auth\TwoFactorController;
use Modules\Admin\Http\Controllers\Auth\PasswordResetController;
use Modules\Admin\Http\Controllers\Auth\LogoutController;
use Modules\Admin\Http\Controllers\Auth\SocialiteController;
use Modules\Admin\Http\Controllers\UserController;

// Auth Routes (Guest only)
Route::prefix('admin')->name('admin.')->middleware(['web', 'guest'])->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password Reset
    Route::get('/password/reset', [PasswordResetController::class, 'showForm'])->name('password.request');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

    // Socialite OAuth
    Route::get('/auth/{provider}', [SocialiteController::class, 'redirect'])->name('socialite.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('socialite.callback');
});

// Two-Factor Authentication (Requires session user_id)
Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    Route::get('/two-factor/verify', [TwoFactorController::class, 'showForm'])->name('two-factor.verify');
    Route::post('/two-factor/verify', [TwoFactorController::class, 'verify'])->name('two-factor.verify.post');
});

// OTP Verification (Requires session otp_user_id)
Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    Route::get('/otp/verify', fn () => view('admin::auth.otp'))->name('otp.verify');
});

// Password Change (Requires auth)
Route::prefix('admin')->name('admin.')->middleware(['web', 'admin'])->group(function () {
    Route::get('/change-password', fn () => view('admin::auth.change-password'))->name('change-password');
});

// Protected Routes (Admin required)
Route::prefix('admin')->name('admin.')->middleware(['web', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Logout
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/roles', [UserController::class, 'roles'])->name('users.roles');
    Route::post('/users/roles', [UserController::class, 'storeRole'])->name('users.roles.store');
    Route::put('/users/roles/{role}', [UserController::class, 'updateRole'])->name('users.roles.update');
    Route::delete('/users/roles/{role}', [UserController::class, 'destroyRole'])->name('users.roles.destroy');
    Route::get('/users/online', [UserController::class, 'onlineUsers'])->name('users.online');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}/role', [UserController::class, 'updateUserRole'])->name('users.role');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Portfolio
    Route::get('/portfolio', [\Modules\Portfolio\Http\Controllers\ProjectsController::class, 'index'])->name('portfolio.index');
    Route::get('/portfolio/categories', [\Modules\Portfolio\Http\Controllers\ProjectsController::class, 'index'])->name('portfolio.categories');

    // Blog (stub)
    Route::get('/blog', fn () => view('admin::stub'))->name('blog.index');
    Route::get('/blog/categories', fn () => view('admin::stub'))->name('blog.categories');
    Route::get('/blog/tags', fn () => view('admin::stub'))->name('blog.tags');

    // Orders (stub)
    Route::get('/orders', fn () => view('admin::stub'))->name('orders.index');
    Route::get('/orders/pending', fn () => view('admin::stub'))->name('orders.pending');

    // Tickets (stub)
    Route::get('/tickets', fn () => view('admin::stub'))->name('tickets.index');
    Route::get('/tickets/open', fn () => view('admin::stub'))->name('tickets.open');

    // Analytics (stub)
    Route::get('/analytics', fn () => view('admin::stub'))->name('analytics.index');

    // Settings
    Route::get('/settings', fn () => view('admin::settings.index'))->name('settings.index');
    Route::get('/settings/security', fn () => view('admin::settings.index'))->name('settings.security');
});