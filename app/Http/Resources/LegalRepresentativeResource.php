<?php

namespace App\Http\Resources;

use App\Models\LegalRepresentative;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LegalRepresentative */
class LegalRepresentativeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'firm_name' => $this->firm_name,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }
}
