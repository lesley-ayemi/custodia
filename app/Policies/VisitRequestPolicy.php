<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\VisitRequest;

class VisitRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function review(User $user, VisitRequest $visitRequest): bool
    {
        return $user->hasRole(Role::Supervisor, Role::Admin);
    }
}
