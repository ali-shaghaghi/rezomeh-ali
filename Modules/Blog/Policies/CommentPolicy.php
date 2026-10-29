<?php

namespace Modules\Blog\Policies;

use App\Models\User;
use Modules\Core\Models\Comment;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_blog', 'view_blog']);
    }

    public function view(User $user, Comment $comment): bool
    {
        return $user->hasAnyPermission(['manage_blog', 'view_blog']);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->hasPermission('manage_blog');
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->hasPermission('manage_blog');
    }
}
