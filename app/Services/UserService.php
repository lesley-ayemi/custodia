<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): User
    {
        return DB::transaction(function () use ($data, $actor) {
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            $this->audit->log($actor, 'created', $user, newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ]);

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor) {
            $auditableChanges = Arr::except($data, 'password');
            $oldValues = $user->only(array_keys($auditableChanges));

            if (array_key_exists('password', $data)) {
                $data['password'] = filled($data['password']) ? Hash::make($data['password']) : null;
            }

            $user->update(array_filter($data, fn ($value) => $value !== null));

            $this->audit->log($actor, 'updated', $user, oldValues: $oldValues, newValues: $auditableChanges);

            return $user;
        });
    }

    public function deactivate(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            $this->audit->log($actor, 'deactivated', $user, oldValues: ['name' => $user->name, 'email' => $user->email]);

            $user->delete();
        });
    }
}
