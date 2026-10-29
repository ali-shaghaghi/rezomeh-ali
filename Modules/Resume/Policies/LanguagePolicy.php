<?php

namespace Modules\Resume\Policies;

use App\Models\User;
use Modules\Resume\Models\Language;

class LanguagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_languages', 'view_languages']);
    }

    public function view(User $user, Language $language): bool
    {
        return $user->hasAnyPermission(['manage_languages', 'view_languages']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_languages');
    }

    public function update(User $user, Language $language): bool
    {
        return $user->hasPermission('manage_languages');
    }

    public function delete(User $user, Language $language): bool
    {
        return $user->hasPermission('manage_languages');
    }
}
