<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Movement;
use App\Models\User;

class MovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function approve(User $user, Movement $movement): bool
    {
        return $user->hasRole(Role::Supervisor, Role::Admin);
    }

    public function manage(User $user, Movement $movement): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }
}
