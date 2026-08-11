<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        if (array_key_exists('password', $data)) {
            $data['password'] = filled($data['password']) ? Hash::make($data['password']) : null;
        }

        $user->update(array_filter($data, fn ($value) => $value !== null));

        return $user;
    }

    public function deactivate(User $user): void
    {
        $user->delete();
    }
}
