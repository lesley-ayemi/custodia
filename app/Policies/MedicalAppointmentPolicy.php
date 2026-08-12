<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\MedicalAppointment;
use App\Models\User;

class MedicalAppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }

    public function manage(User $user, MedicalAppointment $medicalAppointment): bool
    {
        return $user->hasRole(Role::Medical, Role::Admin);
    }
}
