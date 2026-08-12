<?php

namespace App\Models;

use App\Enums\MedicalAlertSeverity;
use Database\Factories\MedicalAlertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @use HasFactory<MedicalAlertFactory> */
#[Fillable([
    'prisoner_id',
    'created_by',
    'message',
    'severity',
    'active',
])]
class MedicalAlert extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity' => MedicalAlertSeverity::class,
            'active' => 'boolean',
        ];
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
