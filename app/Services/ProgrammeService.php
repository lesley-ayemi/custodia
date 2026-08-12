<?php

namespace App\Services;

use App\Enums\EnrolmentStatus;
use App\Enums\ProgrammeStatus;
use App\Models\Prisoner;
use App\Models\Programme;
use App\Models\ProgrammeAttendance;
use App\Models\ProgrammeEnrolment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ProgrammeService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createProgramme(array $data): Programme
    {
        $data['status'] ??= ProgrammeStatus::Active;

        return Programme::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProgramme(Programme $programme, array $data): Programme
    {
        $programme->update($data);

        return $programme;
    }

    public function deleteProgramme(Programme $programme): void
    {
        if ($programme->enrolments()->exists()) {
            throw ValidationException::withMessages([
                'programme' => 'This programme has enrolment history and cannot be deleted.',
            ]);
        }

        $programme->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function enrol(Programme $programme, Prisoner $prisoner, User $enrolledBy, array $data): ProgrammeEnrolment
    {
        $alreadyEnrolled = $programme->enrolments()
            ->where('prisoner_id', $prisoner->id)
            ->where('status', EnrolmentStatus::Enrolled)
            ->exists();

        if ($alreadyEnrolled) {
            throw ValidationException::withMessages([
                'programme' => 'This prisoner is already enrolled in this programme.',
            ]);
        }

        $data['programme_id'] = $programme->id;
        $data['prisoner_id'] = $prisoner->id;
        $data['enrolled_by'] = $enrolledBy->id;
        $data['status'] ??= EnrolmentStatus::Enrolled;

        return ProgrammeEnrolment::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordAttendance(ProgrammeEnrolment $enrolment, User $recordedBy, array $data): ProgrammeAttendance
    {
        $data['programme_enrolment_id'] = $enrolment->id;
        $data['recorded_by'] = $recordedBy->id;
        $data['attended'] ??= true;

        return ProgrammeAttendance::create($data);
    }

    public function complete(ProgrammeEnrolment $enrolment): ProgrammeEnrolment
    {
        $this->guardActive($enrolment);

        $enrolment->status = EnrolmentStatus::Completed;
        $enrolment->completed_at = now();
        $enrolment->save();

        return $enrolment;
    }

    public function withdraw(ProgrammeEnrolment $enrolment, ?string $reason): ProgrammeEnrolment
    {
        $this->guardActive($enrolment);

        $enrolment->status = EnrolmentStatus::Withdrawn;
        $enrolment->withdrawal_reason = $reason;
        $enrolment->save();

        return $enrolment;
    }

    protected function guardActive(ProgrammeEnrolment $enrolment): void
    {
        if ($enrolment->status !== EnrolmentStatus::Enrolled) {
            throw ValidationException::withMessages([
                'status' => 'This enrolment has already been finalised.',
            ]);
        }
    }
}
