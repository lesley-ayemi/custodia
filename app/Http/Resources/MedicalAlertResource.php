<?php

namespace App\Http\Resources;

use App\Models\MedicalAlert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MedicalAlert */
class MedicalAlertResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prisoner_id' => $this->prisoner_id,
            'message' => $this->message,
            'severity' => $this->severity->value,
            'active' => $this->active,
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy->name),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
