<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Prisoner */
class PrisonerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prisoner_number' => $this->prisoner_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->fullName(),
            'date_of_birth' => $this->date_of_birth->toDateString(),
            'gender' => $this->gender->value,
            'admission_date' => $this->admission_date->toDateString(),
            'expected_release_date' => $this->expected_release_date?->toDateString(),
            'status' => $this->status->value,
            'photo_path' => $this->photo_path,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'current_cell' => $this->whenLoaded('currentHousing', fn () => $this->currentHousing ? [
                'block_name' => $this->currentHousing->cell->block->name,
                'cell_code' => $this->currentHousing->cell->code,
            ] : null),
        ];
    }
}
