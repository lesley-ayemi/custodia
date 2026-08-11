<?php

namespace App\Models;

use App\Enums\HearingStatus;
use App\Enums\HearingType;
use Database\Factories\CourtHearingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @use HasFactory<CourtHearingFactory> */
#[Fillable(['court_case_id', 'type', 'scheduled_at', 'location', 'status', 'outcome', 'notes'])]
class CourtHearing extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'type' => HearingType::class,
            'status' => HearingStatus::class,
        ];
    }

    public function courtCase(): BelongsTo
    {
        return $this->belongsTo(CourtCase::class);
    }
}
