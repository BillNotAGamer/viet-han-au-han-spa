<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TrainingCourse;
use App\Models\User;

class TrainingCoursePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, TrainingCourse $course): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, TrainingCourse $course): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, TrainingCourse $course): bool
    {
        return (bool) $user->is_admin;
    }
}
