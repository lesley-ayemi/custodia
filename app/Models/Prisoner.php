<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\PrisonerStatus;
use Database\Factories\PrisonerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** @use HasFactory<PrisonerFactory> */
#[Fillable([
    'prisoner_number',
    'first_name',
    'last_name',
    'date_of_birth',
    'gender',
    'admission_date',
    'expected_release_date',
    'status',
    'photo_path',
])]
class Prisoner extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'expected_release_date' => 'date',
            'archived_at' => 'datetime',
            'gender' => Gender::class,
            'status' => PrisonerStatus::class,
        ];
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function housingAssignments(): HasMany
    {
        return $this->hasMany(HousingAssignment::class)->orderByDesc('started_at');
    }

    public function currentHousing(): HasOne
    {
        return $this->hasOne(HousingAssignment::class)->whereNull('ended_at')->latestOfMany('started_at');
    }

    public function courtCases(): HasMany
    {
        return $this->hasMany(CourtCase::class)->orderByDesc('opened_at');
    }

    public function propertyItems(): HasMany
    {
        return $this->hasMany(PropertyItem::class)->orderByDesc('received_at');
    }
}
