<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Block */
class BlockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cells' => $this->cells->map(fn ($cell) => [
                'id' => $cell->id,
                'code' => $cell->code,
                'capacity' => $cell->capacity,
                'occupancy' => $cell->occupancy(),
                'available' => $cell->availableBeds(),
            ]),
        ];
    }
}
