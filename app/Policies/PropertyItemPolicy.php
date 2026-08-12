<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\PropertyItem;
use App\Models\User;

class PropertyItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function view(User $user, PropertyItem $propertyItem): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }

    public function release(User $user, PropertyItem $propertyItem): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }
}
