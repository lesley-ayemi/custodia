<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\CourtCase;
use App\Models\User;

class CourtCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function view(User $user, CourtCase $courtCase): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function update(User $user, CourtCase $courtCase): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }
}
