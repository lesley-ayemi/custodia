<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Cell;
use App\Models\HousingAssignment;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HousingService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    public function assign(Prisoner $prisoner, Cell $cell, User $assignedBy): HousingAssignment
    {
        return DB::transaction(function () use ($prisoner, $cell, $assignedBy) {
            $previousCell = $prisoner->currentHousing?->cell?->code;

            $prisoner->housingAssignments()->whereNull('ended_at')->update(['ended_at' => now()]);

            $assignment = HousingAssignment::create([
                'prisoner_id' => $prisoner->id,
                'cell_id' => $cell->id,
                'assigned_by' => $assignedBy->id,
                'started_at' => now(),
            ]);

            $this->audit->log(
                $assignedBy,
                'housing assignment changed',
                $prisoner,
                oldValues: ['cell' => $previousCell],
                newValues: ['cell' => $cell->code],
            );

            return $assignment;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createBlock(array $data, User $actor): Block
    {
        return DB::transaction(function () use ($data, $actor) {
            $block = Block::create($data);

            $this->audit->log($actor, 'created', $block, newValues: ['name' => $block->name]);

            return $block;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateBlock(Block $block, array $data, User $actor): Block
    {
        return DB::transaction(function () use ($block, $data, $actor) {
            $oldValues = $block->only(array_keys($data));

            $block->update($data);

            $this->audit->log($actor, 'updated', $block, oldValues: $oldValues, newValues: $data);

            return $block;
        });
    }

    public function deleteBlock(Block $block, User $actor): void
    {
        DB::transaction(function () use ($block, $actor) {
            if ($block->cells()->exists()) {
                throw ValidationException::withMessages([
                    'block' => 'Delete or move all cells out of this block first.',
                ]);
            }

            $name = $block->name;

            $block->delete();

            $this->audit->log($actor, 'deleted', $block, oldValues: ['name' => $name]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCell(array $data, User $actor): Cell
    {
        return DB::transaction(function () use ($data, $actor) {
            $cell = Cell::create($data);

            $this->audit->log($actor, 'created', $cell, newValues: [
                'code' => $cell->code,
                'capacity' => $cell->capacity,
            ]);

            return $cell;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCell(Cell $cell, array $data, User $actor): Cell
    {
        return DB::transaction(function () use ($cell, $data, $actor) {
            if (isset($data['capacity']) && $data['capacity'] < $cell->occupancy()) {
                throw ValidationException::withMessages([
                    'capacity' => "Capacity cannot be less than the current occupancy ({$cell->occupancy()}).",
                ]);
            }

            $oldValues = $cell->only(array_keys($data));

            $cell->update($data);

            $this->audit->log($actor, 'updated', $cell, oldValues: $oldValues, newValues: $data);

            return $cell;
        });
    }

    public function deleteCell(Cell $cell, User $actor): void
    {
        DB::transaction(function () use ($cell, $actor) {
            if ($cell->housingAssignments()->exists()) {
                throw ValidationException::withMessages([
                    'cell' => 'This cell has housing history and cannot be deleted.',
                ]);
            }

            $code = $cell->code;

            $cell->delete();

            $this->audit->log($actor, 'deleted', $cell, oldValues: ['code' => $code]);
        });
    }
}
