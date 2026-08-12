<?php

namespace App\Models;

use App\Enums\VisitRequestStatus;
use Database\Factories\VisitRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** @use HasFactory<VisitRequestFactory> */
#[Fillable([
    'visitor_id',
    'prisoner_id',
    'relationship',
    'requested_by',
    'requested_visit_date',
    'status',
    'rejection_reason',
])]
class VisitRequest extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_visit_date' => 'date',
            'status' => VisitRequestStatus::class,
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }
}
