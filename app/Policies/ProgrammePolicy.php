<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class ProgrammePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function manage(User $user): bool
    {
        return $user->hasRole(Role::Admin);
    }
}
