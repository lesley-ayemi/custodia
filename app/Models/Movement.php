<?php

namespace App\Models;

use App\Enums\MovementStatus;
use Database\Factories\MovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @use HasFactory<MovementFactory> */
#[Fillable([
    'prisoner_id',
    'from_location',
    'to_location',
    'reason',
    'requested_by',
    'approved_by',
    'scheduled_at',
    'departed_at',
    'arrived_at',
    'returned_at',
    'status',
])]
class Movement extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'departed_at' => 'datetime',
            'arrived_at' => 'datetime',
            'returned_at' => 'datetime',
            'status' => MovementStatus::class,
        ];
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
