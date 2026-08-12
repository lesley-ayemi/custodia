<?php

namespace App\Models;

use App\Enums\AdmissionStatus;
use App\Enums\SecurityClassification;
use Database\Factories\AdmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @use HasFactory<AdmissionFactory> */
#[Fillable([
    'prisoner_id',
    'admitted_by',
    'admission_date',
    'admission_reason',
    'legal_authority_reference',
    'initial_assessment_notes',
    'security_classification',
    'status',
    'completed_at',
])]
class Admission extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'completed_at' => 'datetime',
            'security_classification' => SecurityClassification::class,
            'status' => AdmissionStatus::class,
        ];
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function admittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }
}
