<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ProgrammeEnrolment;
use App\Models\User;

class ProgrammeEnrolmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function manage(User $user, ProgrammeEnrolment $enrolment): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }
}
