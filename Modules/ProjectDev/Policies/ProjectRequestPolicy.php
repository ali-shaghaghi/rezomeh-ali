<?php

namespace Modules\ProjectDev\Policies;

use App\Models\User;
use Modules\ProjectDev\Models\ProjectRequest;

class ProjectRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_projects', 'view_projects']);
    }

    public function view(User $user, ProjectRequest $request): bool
    {
        return $user->hasAnyPermission(['manage_projects', 'view_projects']);
    }

    public function create(User $user): bool
    {
        // هر کاربر واردشده می‌تواند درخواست پروژه ثبت کند
        return (bool) $user->is_active;
    }

    public function update(User $user, ProjectRequest $request): bool
    {
        return $user->hasPermission('manage_projects');
    }

    public function delete(User $user, ProjectRequest $request): bool
    {
        return $user->hasPermission('manage_projects');
    }
}
