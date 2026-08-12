<?php

namespace App\Http\Resources;

use App\Models\PropertyItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PropertyItem */
class PropertyItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prisoner_id' => $this->prisoner_id,
            'property_number' => $this->property_number,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'storage_location' => $this->storage_location,
            'received_by' => $this->whenLoaded('receivedBy', fn () => $this->receivedBy->name),
            'received_at' => $this->received_at->toIso8601String(),
            'released_by' => $this->whenLoaded('releasedBy', fn () => $this->releasedBy?->name),
            'released_at' => $this->released_at?->toIso8601String(),
        ];
    }
}
