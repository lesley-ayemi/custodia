<?php

namespace App\Http\Resources;

use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Programme */
class ProgrammeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category->value,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'status' => $this->status->value,
            'enrolled_count' => $this->whenCounted('enrolments'),
        ];
    }
}
