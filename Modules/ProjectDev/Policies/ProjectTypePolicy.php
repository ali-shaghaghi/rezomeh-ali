<?php

namespace Modules\ProjectDev\Policies;

use App\Models\User;
use Modules\ProjectDev\Models\ProjectType;

class ProjectTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_projects', 'view_projects']);
    }

    public function view(User $user, ProjectType $type): bool
    {
        return $user->hasAnyPermission(['manage_projects', 'view_projects']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_projects');
    }

    public function update(User $user, ProjectType $type): bool
    {
        return $user->hasPermission('manage_projects');
    }

    public function delete(User $user, ProjectType $type): bool
    {
        return $user->hasPermission('manage_projects');
    }
}
