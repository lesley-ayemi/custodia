<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Sentence;
use App\Models\User;

class SentencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function view(User $user, Sentence $sentence): bool
    {
        return $user->hasRole(Role::Admin, Role::Officer, Role::Supervisor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Officer, Role::Admin);
    }
}
