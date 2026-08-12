<?php

namespace App\Models;

use App\Enums\MedicalAppointmentStatus;
use Database\Factories\MedicalAppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @use HasFactory<MedicalAppointmentFactory> */
#[Fillable([
    'prisoner_id',
    'scheduled_by',
    'appointment_type',
    'provider',
    'location',
    'scheduled_at',
    'status',
    'notes',
])]
class MedicalAppointment extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'status' => MedicalAppointmentStatus::class,
        ];
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }
}
