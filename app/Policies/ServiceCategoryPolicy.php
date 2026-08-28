<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;

class ServiceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, ServiceCategory $category): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, ServiceCategory $category): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, ServiceCategory $category): bool
    {
        return (bool) $user->is_admin;
    }
}
