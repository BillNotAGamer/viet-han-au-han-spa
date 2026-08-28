<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, SiteSetting $setting): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, SiteSetting $setting): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, SiteSetting $setting): bool
    {
        return (bool) $user->is_admin;
    }
}
