<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class MedicalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }
}
