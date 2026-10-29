<?php

namespace Modules\Resume\Policies;

use App\Models\User;
use Modules\Resume\Models\Skill;

class SkillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_skills', 'view_skills']);
    }

    public function view(User $user, Skill $skill): bool
    {
        return $user->hasAnyPermission(['manage_skills', 'view_skills']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_skills');
    }

    public function update(User $user, Skill $skill): bool
    {
        return $user->hasPermission('manage_skills');
    }

    public function delete(User $user, Skill $skill): bool
    {
        return $user->hasPermission('manage_skills');
    }
}
