<?php

namespace App\Models;

use App\Enums\LegalStatus;
use App\Enums\SentenceType;
use Database\Factories\SentenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @use HasFactory<SentenceFactory> */
#[Fillable([
    'prisoner_id',
    'case_number',
    'court',
    'offence',
    'sentence_start',
    'sentence_end',
    'sentence_type',
    'parole_eligibility_date',
    'legal_status',
])]
class Sentence extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sentence_start' => 'date',
            'sentence_end' => 'date',
            'parole_eligibility_date' => 'date',
            'sentence_type' => SentenceType::class,
            'legal_status' => LegalStatus::class,
        ];
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }
}
