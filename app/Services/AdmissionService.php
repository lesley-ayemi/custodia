<?php

namespace App\Services;

use App\Enums\AdmissionStatus;
use App\Models\Admission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdmissionService
{
    public function __construct(
        protected AuditService $audit,
        protected PrisonerService $prisoners,
    ) {}

    /**
     * @param  array<string, mixed>  $prisonerData
     * @param  array<string, mixed>  $admissionData
     */
    public function start(array $prisonerData, array $admissionData, User $actor): Admission
    {
        return DB::transaction(function () use ($prisonerData, $admissionData, $actor) {
            $prisonerData['admission_date'] = $admissionData['admission_date'];

            $prisoner = $this->prisoners->create($prisonerData, $actor);

            $admission = Admission::create([
                'prisoner_id' => $prisoner->id,
                'admitted_by' => $actor->id,
                'admission_date' => $admissionData['admission_date'],
                'admission_reason' => $admissionData['admission_reason'],
                'status' => AdmissionStatus::Draft,
            ]);

            $this->audit->log($actor, 'started admission', $admission, newValues: [
                'prisoner' => $prisoner->fullName(),
                'admission_reason' => $admission->admission_reason,
            ]);

            return $admission;
        });
    }

    public function recordLegalAuthority(Admission $admission, string $reference, User $actor): Admission
    {
        return DB::transaction(function () use ($admission, $reference, $actor) {
            $this->guardStatus($admission, AdmissionStatus::Draft);

            $admission->legal_authority_reference = $reference;
            $admission->status = AdmissionStatus::Processing;
            $admission->save();

            $this->audit->log($actor, 'recorded legal authority', $admission, newValues: ['legal_authority_reference' => $reference]);

            return $admission;
        });
    }

    public function recordInitialAssessment(Admission $admission, string $notes, User $actor): Admission
    {
        return DB::transaction(function () use ($admission, $notes, $actor) {
            $this->guardStatus($admission, AdmissionStatus::Processing);

            $admission->initial_assessment_notes = $notes;
            $admission->save();

            $this->audit->log($actor, 'recorded initial assessment', $admission);

            return $admission;
        });
    }

    public function recordSecurityClassification(Admission $admission, string $classification, User $actor): Admission
    {
        return DB::transaction(function () use ($admission, $classification, $actor) {
            $this->guardStatus($admission, AdmissionStatus::Processing);

            $admission->security_classification = $classification;
            $admission->save();

            $this->audit->log($actor, 'recorded security classification', $admission, newValues: ['security_classification' => $classification]);

            return $admission;
        });
    }

    public function advanceToMedicalScreening(Admission $admission, User $actor): Admission
    {
        return DB::transaction(function () use ($admission, $actor) {
            $this->guardStatus($admission, AdmissionStatus::Processing);

            if ($admission->security_classification === null) {
                throw ValidationException::withMessages([
                    'security_classification' => 'Record a security classification before sending this admission for medical screening.',
                ]);
            }

            $admission->status = AdmissionStatus::AwaitingMedical;
            $admission->save();

            $this->audit->log($actor, 'sent for medical screening', $admission, newValues: ['status' => 'awaiting_medical']);

            return $admission;
        });
    }

    public function completeMedicalScreening(Admission $admission, User $actor): Admission
    {
        return DB::transaction(function () use ($admission, $actor) {
            $this->guardStatus($admission, AdmissionStatus::AwaitingMedical);

            $admission->status = AdmissionStatus::AwaitingHousing;
            $admission->save();

            $this->audit->log($actor, 'completed medical screening', $admission, newValues: ['status' => 'awaiting_housing']);

            return $admission;
        });
    }

    public function completeHousingAssignment(Admission $admission, User $actor): Admission
    {
        return DB::transaction(function () use ($admission, $actor) {
            $this->guardStatus($admission, AdmissionStatus::AwaitingHousing);

            if (! $admission->prisoner->currentHousing()->exists()) {
                throw ValidationException::withMessages([
                    'housing' => 'Assign this prisoner to a cell before completing admission.',
                ]);
            }

            $admission->status = AdmissionStatus::Completed;
            $admission->completed_at = now();
            $admission->save();

            $this->audit->log($actor, 'completed admission', $admission, newValues: ['status' => 'completed']);

            return $admission;
        });
    }

    protected function guardStatus(Admission $admission, AdmissionStatus $expected): void
    {
        if ($admission->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "This admission must be {$expected->value} for that action.",
            ]);
        }
    }
}
