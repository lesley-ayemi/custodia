<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }

    public function manage(User $user, Prescription $prescription): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }
}
