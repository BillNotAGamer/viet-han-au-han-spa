<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Booking $booking): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Booking $booking): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

    public function restore(User $user, Booking $booking): bool
    {
        return (bool) $user->is_admin;
    }

    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
