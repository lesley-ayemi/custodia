<?php

namespace App\Services;

use App\Enums\MovementStatus;
use App\Models\Movement;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovementService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function request(Prisoner $prisoner, User $actor, array $data): Movement
    {
        return DB::transaction(function () use ($prisoner, $actor, $data) {
            $data['prisoner_id'] = $prisoner->id;
            $data['requested_by'] = $actor->id;
            $data['status'] ??= MovementStatus::Requested;

            $movement = Movement::create($data);

            $this->audit->log($actor, 'requested movement', $movement, newValues: [
                'to_location' => $movement->to_location,
                'reason' => $movement->reason,
            ]);

            return $movement;
        });
    }

    public function approve(Movement $movement, User $actor): Movement
    {
        return DB::transaction(function () use ($movement, $actor) {
            $this->guardStatus($movement, MovementStatus::Requested);

            $movement->status = MovementStatus::Approved;
            $movement->approved_by = $actor->id;
            $movement->save();

            $this->audit->log($actor, 'approved movement', $movement, newValues: ['status' => 'approved']);

            return $movement;
        });
    }

    public function markDeparted(Movement $movement, User $actor): Movement
    {
        return DB::transaction(function () use ($movement, $actor) {
            $this->guardStatus($movement, MovementStatus::Approved);

            $movement->status = MovementStatus::Departed;
            $movement->departed_at = now();
            $movement->save();

            $this->audit->log($actor, 'departed', $movement, newValues: ['status' => 'departed']);

            return $movement;
        });
    }

    public function markArrived(Movement $movement, User $actor): Movement
    {
        return DB::transaction(function () use ($movement, $actor) {
            $this->guardStatus($movement, MovementStatus::Departed);

            $movement->status = MovementStatus::Arrived;
            $movement->arrived_at = now();
            $movement->save();

            $this->audit->log($actor, 'arrived', $movement, newValues: ['status' => 'arrived']);

            return $movement;
        });
    }

    public function markReturned(Movement $movement, User $actor): Movement
    {
        return DB::transaction(function () use ($movement, $actor) {
            $this->guardStatus($movement, MovementStatus::Arrived);

            $movement->status = MovementStatus::Returned;
            $movement->returned_at = now();
            $movement->save();

            $this->audit->log($actor, 'returned', $movement, newValues: ['status' => 'returned']);

            return $movement;
        });
    }

    public function cancel(Movement $movement, User $actor): Movement
    {
        return DB::transaction(function () use ($movement, $actor) {
            if (! in_array($movement->status, [MovementStatus::Requested, MovementStatus::Approved], true)) {
                throw ValidationException::withMessages([
                    'status' => 'A movement can only be cancelled before it departs.',
                ]);
            }

            $movement->status = MovementStatus::Cancelled;
            $movement->save();

            $this->audit->log($actor, 'cancelled movement', $movement, newValues: ['status' => 'cancelled']);

            return $movement;
        });
    }

    protected function guardStatus(Movement $movement, MovementStatus $expected): void
    {
        if ($movement->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "This movement must be {$expected->value} first.",
            ]);
        }
    }
}
