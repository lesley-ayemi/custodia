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
            'block_id' => $this->block_id,
            'code' => $this->code,
            'capacity' => $this->capacity,
            'occupancy' => $this->occupancy(),
            'available' => $this->availableBeds(),
        ];
    }
}
