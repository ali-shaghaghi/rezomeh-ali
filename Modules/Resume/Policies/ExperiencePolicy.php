<?php

namespace Modules\Resume\Policies;

use App\Models\User;
use Modules\Resume\Models\Experience;

class ExperiencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_experience', 'view_experience']);
    }

    public function view(User $user, Experience $experience): bool
    {
        return $user->hasAnyPermission(['manage_experience', 'view_experience']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_experience');
    }

    public function update(User $user, Experience $experience): bool
    {
        return $user->hasPermission('manage_experience');
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $user->hasPermission('manage_experience');
    }
}
