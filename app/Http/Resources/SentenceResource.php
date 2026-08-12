<?php

namespace App\Http\Resources;

use App\Models\Sentence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Sentence */
class SentenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prisoner_id' => $this->prisoner_id,
            'prisoner_name' => $this->whenLoaded('prisoner', fn () => $this->prisoner->fullName()),
            'case_number' => $this->case_number,
            'court' => $this->court,
            'offence' => $this->offence,
            'sentence_start' => $this->sentence_start->toDateString(),
            'sentence_end' => $this->sentence_end?->toDateString(),
            'sentence_type' => $this->sentence_type->value,
            'parole_eligibility_date' => $this->parole_eligibility_date?->toDateString(),
            'legal_status' => $this->legal_status->value,
        ];
    }
}
