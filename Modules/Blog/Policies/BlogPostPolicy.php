<?php

namespace Modules\Blog\Policies;

use App\Models\User;
use Modules\Core\Models\BlogPost;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_blog', 'view_blog']);
    }

    public function view(User $user, BlogPost $post): bool
    {
        return $user->hasAnyPermission(['manage_blog', 'view_blog']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_blog');
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $user->hasPermission('manage_blog');
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $user->hasPermission('manage_blog');
    }
}
