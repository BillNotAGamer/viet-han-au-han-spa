<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TrainingInquiry;
use App\Models\User;

class TrainingInquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, TrainingInquiry $inquiry): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        // Public/Admin creation is forbidden in Phase 5
        return false;
    }

    public function update(User $user, TrainingInquiry $inquiry): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, TrainingInquiry $inquiry): bool
    {
        return (bool) $user->is_admin;
    }

    public function restore(User $user, TrainingInquiry $inquiry): bool
    {
        return (bool) $user->is_admin;
    }

    public function forceDelete(User $user, TrainingInquiry $inquiry): bool
    {
        // Force delete is strictly prohibited
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
