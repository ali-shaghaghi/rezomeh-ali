<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Admin\Exports\UsersExport;
use Modules\Admin\Imports\UsersImport;
use Modules\Core\Models\Role;
use Modules\Core\Models\Permission;
use Modules\Core\Models\UserActivity;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
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

        return view('admin::users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('roles');
        $isOnline = UserActivity::isOnline($user->id);
        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('last_active_at', 'desc')
            ->take(10)
            ->get();

        return view('admin::users.show', compact('user', 'isOnline', 'activities'));
    }

    public function roles()
    {
        $roles = Role::withCount('users')->with('permissions')->get();
        $permissions = Permission::all();
        return view('admin::users.roles', compact('roles', 'permissions'));
    }

    public function storeRole()
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create([
            'name' => request('name'),
            'slug' => request('slug'),
            'description' => request('description'),
        ]);

        if (request('permissions')) {
            $role->permissions()->attach(request('permissions'));
        }

        return redirect()->route('admin.users.roles')->with('success', 'نقش جدید اضافه شد.');
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.users.roles')->with('success', 'نقش ویرایش شد.');
    }

    public function destroyRole(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.users.roles')->with('success', 'نقش حذف شد.');
    }

    public function updateUserRole(User $user)
    {
        request()->validate([
            'role' => 'required|exists:roles,slug',
        ]);

        $role = Role::where('slug', request('role'))->first();
        $user->roles()->sync([$role->id]);

        return back()->with('success', 'نقش کاربر تغییر کرد.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'فعال' : 'غیرفعال';
        return back()->with('success', "کاربر {$status} شد.");
    }

    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new UsersImport;
            Excel::import($import, $request->file('file'));

            $message = "✅ {$import->created} کاربر جدید ایجاد شد، {$import->updated} کاربر به‌روزرسانی شد.";

            if ($import->skipped > 0) {
                $message .= " ⚠️ {$import->skipped} ردیف رد شد.";
            }

            if (!empty($import->errors)) {
                $message .= ' ' . implode(' | ', array_slice($import->errors, 0, 5));
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'خطایی در وارد کردن فایل رخ داد: ' . $e->getMessage());
        }
    }

    public function onlineUsers()
    {
        $onlineUsers = UserActivity::getOnlineUsers(5);
        return view('admin::users.online', compact('onlineUsers'));
    }
}