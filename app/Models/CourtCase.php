<?php

namespace App\Models;

use App\Enums\CourtCaseStatus;
use Database\Factories\CourtCaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @use HasFactory<CourtCaseFactory> */
#[Fillable([
    'case_number',
    'prisoner_id',
    'legal_representative_id',
    'court_name',
    'charge',
    'status',
    'opened_at',
    'closed_at',
])]
class CourtCase extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opened_at' => 'date',
            'closed_at' => 'date',
            'status' => CourtCaseStatus::class,
        ];
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function legalRepresentative(): BelongsTo
    {
        return $this->belongsTo(LegalRepresentative::class);
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(CourtHearing::class)->orderBy('scheduled_at');
    }
}
