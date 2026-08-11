<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Prisoner;
use App\Models\User;

class PrisonerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function view(User $user, Prisoner $prisoner): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function update(User $user, Prisoner $prisoner): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function archive(User $user, Prisoner $prisoner): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }
}
