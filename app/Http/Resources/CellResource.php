<?php

namespace App\Http\Resources;

use App\Models\Cell;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Cell */
class CellResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wing_id' => $this->wing_id,
            'wing_name' => $this->wing->name,
            'block_id' => $this->wing->block_id,
            'block_name' => $this->wing->block->name,
            'code' => $this->code,
            'capacity' => $this->capacity,
            'occupancy' => $this->occupancy(),
            'available' => $this->availableBeds(),
        ];
    }
}
