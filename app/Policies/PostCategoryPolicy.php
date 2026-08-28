<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PostCategory;
use App\Models\User;

class PostCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, PostCategory $category): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, PostCategory $category): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, PostCategory $category): bool
    {
        return (bool) $user->is_admin;
    }
}
