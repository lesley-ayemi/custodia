<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function manage(User $user, Visit $visit): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }
}
