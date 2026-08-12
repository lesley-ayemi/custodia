<?php

namespace App\Http\Resources;

use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Block */
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
            'wings' => $this->wings->map(fn ($wing) => [
                'id' => $wing->id,
                'name' => $wing->name,
                'cells' => $wing->cells->map(fn ($cell) => [
                    'id' => $cell->id,
                    'code' => $cell->code,
                    'capacity' => $cell->capacity,
                    'occupancy' => $cell->occupancy(),
                    'available' => $cell->availableBeds(),
                ]),
            ]),
        ];
    }
}
