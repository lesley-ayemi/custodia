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

    public function sentences(): HasMany
    {
        return $this->hasMany(Sentence::class)->orderByDesc('sentence_start');
    }

    public function propertyItems(): HasMany
    {
        return $this->hasMany(PropertyItem::class)->orderByDesc('received_at');
    }

    public function programmeEnrolments(): HasMany
    {
        return $this->hasMany(ProgrammeEnrolment::class)->orderByDesc('enrolled_at');
    }

    public function releaseReviews(): HasMany
    {
        return $this->hasMany(ReleaseReview::class)->orderByDesc('initiated_at');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class)->orderByDesc('recorded_at');
    }

    public function medicalAppointments(): HasMany
    {
        return $this->hasMany(MedicalAppointment::class)->orderByDesc('scheduled_at');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class)->orderByDesc('start_date');
    }

    public function medicalAlerts(): HasMany
    {
        return $this->hasMany(MedicalAlert::class)->orderByDesc('created_at');
    }

    public function visitRequests(): HasMany
    {
        return $this->hasMany(VisitRequest::class)->orderByDesc('requested_visit_date');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class)->orderByDesc('scheduled_at');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class)->orderByDesc('scheduled_at');
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class)->orderByDesc('admission_date');
    }
}
