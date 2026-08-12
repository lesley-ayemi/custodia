<?php

namespace App\Models;

use App\Enums\ReleaseReviewStatus;
use Database\Factories\ReleaseReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @use HasFactory<ReleaseReviewFactory> */
#[Fillable([
    'prisoner_id',
    'initiated_by',
    'initiated_at',
    'status',
    'released_at',
    'cancelled_at',
    'cancellation_reason',
])]
class ReleaseReview extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initiated_at' => 'datetime',
            'released_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => ReleaseReviewStatus::class,
        ];
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ReleaseReviewStep::class)->orderBy('completed_at');
    }
}
