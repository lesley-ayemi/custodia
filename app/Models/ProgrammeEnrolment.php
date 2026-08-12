<?php

namespace App\Models;

use App\Enums\EnrolmentStatus;
use Database\Factories\ProgrammeEnrolmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @use HasFactory<ProgrammeEnrolmentFactory> */
#[Fillable([
    'programme_id',
    'prisoner_id',
    'enrolled_by',
    'enrolled_at',
    'status',
    'completed_at',
    'withdrawal_reason',
])]
class ProgrammeEnrolment extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrolled_at' => 'date',
            'completed_at' => 'date',
            'status' => EnrolmentStatus::class,
        ];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ProgrammeAttendance::class)->orderByDesc('session_date');
    }
}
