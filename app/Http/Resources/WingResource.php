<?php

namespace App\Http\Resources;

use App\Models\Wing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Wing */
class WingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'block_id' => $this->block_id,
            'name' => $this->name,
        ];
    }
}
