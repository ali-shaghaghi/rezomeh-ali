<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Auth\Services\AdminLoginToken;
use Modules\Core\Models\Role;
use Modules\Settings\Models\Setting;

/**
 * مدیریت ادمین‌ها (کادر فنی) — تب «ادمین‌ها» در پنل مدیریت.
 *
 * این حساب‌ها فقط از داخل پنل ادمین ساخته/ویرایش/حذف می‌شوند؛
 * هرگز از طریق ثبت‌نام عمومی یا جدول users ایجاد نمی‌شوند.
 */
class AdminStaffController extends Controller
{
    /**
     * لیست ادمین‌ها.
     */
    public function index()
    {
        $admins = Admin::with('roles')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->when(request('role'), function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('slug', $role);
                });
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // نقش‌های قابل انتساب به کادر فنی — نقش «کاربر» هرگز به ادمین داده نمی‌شود
        $roles = Role::where('slug', '!=', 'user')->get();

        // لینک ورود هش‌شده سراسری — برای ارسال به ادمین‌های دیگر (بدون توکن اختصاصی)
        $globalLoginUrl = AdminLoginToken::globalUrl();
        $canRegenerateTokens = auth('admin')->user()?->hasAnyRole(['super-admin', 'admin']);

        return view('users::admins', compact('admins', 'roles', 'globalLoginUrl', 'canRegenerateTokens'));
    }

    /**
     * بازتولید توکن سراسری ورود ادمین — لینک قبلی همه ادمین‌ها باطل می‌شود.
     * فقط سوپر ادمین/ادمین ارشد.
     */
    public function regenerateGlobalRouteToken()
    {
        abort_unless(auth('admin')->user()?->hasAnyRole(['super-admin', 'admin']), 403, 'فقط مدیر ارشد می‌تواند توکن را بازتولید کند.');

        Setting::set('admin_login_token', AdminLoginToken::generateGlobal(), 'security');

        return back()->with('success', 'روت سراسری ورود ادمین بازتولید شد — لینک قبلی دیگر معتبر نیست.');
    }

    /**
     * تولید/بازتولید روت اختصاصی یک ادمین (admins.login_route_token).
     * فقط سوپر ادمین/ادمین ارشد.
     */
    public function regenerateRouteToken(Admin $admin)
    {
        abort_unless(auth('admin')->user()?->hasAnyRole(['super-admin', 'admin']), 403, 'فقط مدیر ارشد می‌تواند توکن را بازتولید کند.');

        $admin->update(['login_route_token' => AdminLoginToken::generatePrivate()]);

        return back()->with('success', "روت اختصاصی ورود «{$admin->name}» بازتولید شد — لینک قبلی او باطل شد.");
    }

    /**
     * ساخت ادمین جدید (فقط از داخل پنل).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:admins,phone', 'regex:/^09[0-9]{9}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,slug', Rule::notIn(['user'])],
        ], [
            'phone.regex' => 'شماره موبایل معتبر نیست (مثال: 09123456789).',
            'role.not_in' => 'نقش «کاربر» برای کادر فنی قابل استفاده نیست.',
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $admin->roles()->attach(Role::where('slug', $data['role'])->firstOrFail());

        return redirect()->route('admin.admins.index')->with('success', 'ادمین جدید اضافه شد.');
    }

    /**
     * ویرایش ادمین.
     */
    public function update(Request $request, Admin $admin)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email,'.$admin->id],
            'phone' => ['required', 'string', 'max:20', 'unique:admins,phone,'.$admin->id, 'regex:/^09[0-9]{9}$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,slug', Rule::notIn(['user'])],
        ], [
            'phone.regex' => 'شماره موبایل معتبر نیست (مثال: 09123456789).',
            'role.not_in' => 'نقش «کاربر» برای کادر فنی قابل استفاده نیست.',
        ]);

        $admin->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            // رمز خالی ارسال شده → تغییر نمی‌کند
            'password' => $data['password'] ?? $admin->password,
        ]);

        $admin->roles()->sync([Role::where('slug', $data['role'])->firstOrFail()->id]);

        return redirect()->route('admin.admins.index')->with('success', 'ادمین ویرایش شد.');
    }

    /**
     * تغییر نقش ادمین (فقط نقش‌های کادر فنی).
     */
    public function updateRole(Request $request, Admin $admin)
    {
        $request->validate([
            'role' => ['required', 'exists:roles,slug', Rule::notIn(['user'])],
        ], [
            'role.not_in' => 'نقش «کاربر» برای کادر فنی قابل استفاده نیست.',
        ]);

        $role = Role::where('slug', $request->input('role'))->firstOrFail();
        $admin->roles()->sync([$role->id]);

        return back()->with('success', 'نقش ادمین تغییر کرد.');
    }

    /**
     * فعال/غیرفعال کردن ادمین (نه خودِ واردشده).
     */
    public function toggleStatus(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'امکان غیرفعال کردن حساب خودتان وجود ندارد.');
        }

        $admin->update(['is_active' => ! $admin->is_active]);

        $status = $admin->is_active ? 'فعال' : 'غیرفعال';

        return back()->with('success', "ادمین {$status} شد.");
    }

    /**
     * حذف ادمین (نه خودِ واردشده).
     */
    public function destroy(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'امکان حذف حساب خودتان وجود ندارد.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', 'ادمین حذف شد.');
    }
}
