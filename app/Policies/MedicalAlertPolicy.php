<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\MedicalAlert;
use App\Models\User;

class MedicalAlertPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor, Role::Medical);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }

    public function manage(User $user, MedicalAlert $medicalAlert): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }
}
