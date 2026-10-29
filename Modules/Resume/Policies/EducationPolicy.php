<?php

namespace Modules\Resume\Policies;

use App\Models\User;
use Modules\Resume\Models\Education;

class EducationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_education', 'view_education']);
    }

    public function view(User $user, Education $education): bool
    {
        return $user->hasAnyPermission(['manage_education', 'view_education']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_education');
    }

    public function update(User $user, Education $education): bool
    {
        return $user->hasPermission('manage_education');
    }

    public function delete(User $user, Education $education): bool
    {
        return $user->hasPermission('manage_education');
    }
}
