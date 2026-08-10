<?php

namespace App\Services;

use App\Models\Cell;
use App\Models\HousingAssignment;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
}
