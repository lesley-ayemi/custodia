<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function view(User $user, User $target): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasRole(Role::Admin) && $user->isNot($target);
    }
}
