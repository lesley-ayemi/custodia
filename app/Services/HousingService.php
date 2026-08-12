<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Cell;
use App\Models\Facility;
use App\Models\HousingAssignment;
use App\Models\Prisoner;
use App\Models\User;
use App\Models\Wing;
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
            // Lock the row so two concurrent assignments can't both read the same
            // free-bed count and overfill the cell between the check and the insert.
            $cell = Cell::whereKey($cell->getKey())->lockForUpdate()->firstOrFail();

            $previousCell = $prisoner->currentHousing?->cell?->code;
            $alreadyInCell = $prisoner->currentHousing?->cell_id === $cell->id;

            if (! $alreadyInCell && $cell->availableBeds() < 1) {
                throw ValidationException::withMessages([
                    'cell_id' => "Cell {$cell->code} is already at capacity ({$cell->capacity}).",
                ]);
            }

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
            $data['facility_id'] = Facility::query()->firstOrFail()->id;

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
            if ($block->wings()->exists()) {
                throw ValidationException::withMessages([
                    'block' => 'Delete or move all wings out of this block first.',
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
    public function createWing(array $data, User $actor): Wing
    {
        return DB::transaction(function () use ($data, $actor) {
            $wing = Wing::create($data);

            $this->audit->log($actor, 'created', $wing, newValues: ['name' => $wing->name, 'block_id' => $wing->block_id]);

            return $wing;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateWing(Wing $wing, array $data, User $actor): Wing
    {
        return DB::transaction(function () use ($wing, $data, $actor) {
            $oldValues = $wing->only(array_keys($data));

            $wing->update($data);

            $this->audit->log($actor, 'updated', $wing, oldValues: $oldValues, newValues: $data);

            return $wing;
        });
    }

    public function deleteWing(Wing $wing, User $actor): void
    {
        DB::transaction(function () use ($wing, $actor) {
            if ($wing->cells()->exists()) {
                throw ValidationException::withMessages([
                    'wing' => 'Delete or move all cells out of this wing first.',
                ]);
            }

            $name = $wing->name;

            $wing->delete();

            $this->audit->log($actor, 'deleted', $wing, oldValues: ['name' => $name]);
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
