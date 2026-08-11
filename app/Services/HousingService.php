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
    public function assign(Prisoner $prisoner, Cell $cell, User $assignedBy): HousingAssignment
    {
        return DB::transaction(function () use ($prisoner, $cell, $assignedBy) {
            $prisoner->housingAssignments()->whereNull('ended_at')->update(['ended_at' => now()]);

            return HousingAssignment::create([
                'prisoner_id' => $prisoner->id,
                'cell_id' => $cell->id,
                'assigned_by' => $assignedBy->id,
                'started_at' => now(),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createBlock(array $data): Block
    {
        return Block::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateBlock(Block $block, array $data): Block
    {
        $block->update($data);

        return $block;
    }

    public function deleteBlock(Block $block): void
    {
        if ($block->cells()->exists()) {
            throw ValidationException::withMessages([
                'block' => 'Delete or move all cells out of this block first.',
            ]);
        }

        $block->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCell(array $data): Cell
    {
        return Cell::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCell(Cell $cell, array $data): Cell
    {
        if (isset($data['capacity']) && $data['capacity'] < $cell->occupancy()) {
            throw ValidationException::withMessages([
                'capacity' => "Capacity cannot be less than the current occupancy ({$cell->occupancy()}).",
            ]);
        }

        $cell->update($data);

        return $cell;
    }

    public function deleteCell(Cell $cell): void
    {
        if ($cell->housingAssignments()->exists()) {
            throw ValidationException::withMessages([
                'cell' => 'This cell has housing history and cannot be deleted.',
            ]);
        }

        $cell->delete();
    }
}
