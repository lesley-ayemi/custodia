<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function log(User $user, string $action, Model $entity, array $oldValues = [], array $newValues = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'entity_type' => class_basename($entity),
            'entity_id' => $entity->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
        ]);
    }
}
