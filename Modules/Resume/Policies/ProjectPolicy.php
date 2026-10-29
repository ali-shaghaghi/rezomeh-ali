<?php

namespace Modules\Resume\Policies;

use App\Models\User;
use Modules\Resume\Models\Project;

class ProjectPolicy
{
    /**
     * مشاهده لیست پروژه‌ها
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_projects', 'view_projects']);
    }

    /**
     * مشاهده یک پروژه
     */
    public function view(User $user, Project $project): bool
    {
        return $user->hasAnyPermission(['manage_projects', 'view_projects']);
    }

    /**
     * ایجاد پروژه
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage_projects');
    }

    /**
     * ویرایش پروژه
     */
    public function update(User $user, Project $project): bool
    {
        return $user->hasPermission('manage_projects');
    }

    /**
     * حذف پروژه
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasPermission('manage_projects');
    }
}
