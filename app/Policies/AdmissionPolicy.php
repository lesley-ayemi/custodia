<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Admission;
use App\Models\User;

class AdmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function manage(User $user, Admission $admission): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function completeMedicalScreening(User $user, Admission $admission): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }
}
