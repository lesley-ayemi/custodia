<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ReleaseReview;
use App\Models\User;

class ReleaseReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function recordStep(User $user, ReleaseReview $releaseReview): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function approve(User $user, ReleaseReview $releaseReview): bool
    {
        return $user->hasRole(Role::Supervisor, Role::Admin);
    }

    public function cancel(User $user, ReleaseReview $releaseReview): bool
    {
        return $user->hasRole(Role::Admin);
    }
}
