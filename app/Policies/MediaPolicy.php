<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use App\Services\Media\MediaReferenceInspector;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Media $media): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Media $media): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Media $media): bool
    {
        if (! $user->is_admin) {
            return false;
        }

        return ! app(MediaReferenceInspector::class)->hasReferences($media);
    }
}
