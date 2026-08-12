<?php

namespace App\Models;

use Database\Factories\ProgrammeAttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @use HasFactory<ProgrammeAttendanceFactory> */
#[Fillable([
    'programme_enrolment_id',
    'session_date',
    'attended',
    'notes',
    'recorded_by',
])]
class ProgrammeAttendance extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'attended' => 'boolean',
        ];
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(ProgrammeEnrolment::class, 'programme_enrolment_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
