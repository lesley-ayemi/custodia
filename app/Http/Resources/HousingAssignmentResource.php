<?php

namespace App\Http\Resources;

use App\Models\HousingAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HousingAssignment */
class HousingAssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'block_name' => $this->cell->block->name,
            'cell_code' => $this->cell->code,
            'assigned_by' => $this->assignedBy->name,
            'started_at' => $this->started_at->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
        ];
    }
}
