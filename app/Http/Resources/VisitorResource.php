<?php

namespace App\Http\Resources;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Visitor */
class VisitorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'date_of_birth' => $this->date_of_birth->toDateString(),
            'id_type' => $this->id_type,
            'id_number' => $this->id_number,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'banned_at' => $this->banned_at?->toIso8601String(),
            'ban_reason' => $this->ban_reason,
        ];
    }
}
