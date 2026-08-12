<?php

namespace App\Services;

use App\Enums\EnrolmentStatus;
use App\Enums\ProgrammeStatus;
use App\Models\Prisoner;
use App\Models\Programme;
use App\Models\ProgrammeAttendance;
use App\Models\ProgrammeEnrolment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgrammeService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createProgramme(array $data, User $actor): Programme
    {
        return DB::transaction(function () use ($data, $actor) {
            $data['status'] ??= ProgrammeStatus::Active;

            $programme = Programme::create($data);

            $this->audit->log($actor, 'created', $programme, newValues: [
                'name' => $programme->name,
                'category' => $programme->category->value,
            ]);

            return $programme;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProgramme(Programme $programme, array $data, User $actor): Programme
    {
        return DB::transaction(function () use ($programme, $data, $actor) {
            $oldValues = $programme->only(array_keys($data));

            $programme->update($data);

            $this->audit->log($actor, 'updated', $programme, oldValues: $oldValues, newValues: $data);

            return $programme;
        });
    }

    public function deleteProgramme(Programme $programme, User $actor): void
    {
        DB::transaction(function () use ($programme, $actor) {
            if ($programme->enrolments()->exists()) {
                throw ValidationException::withMessages([
                    'programme' => 'This programme has enrolment history and cannot be deleted.',
                ]);
            }

            $name = $programme->name;

            $programme->delete();

            $this->audit->log($actor, 'deleted', $programme, oldValues: ['name' => $name]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function enrol(Programme $programme, Prisoner $prisoner, User $enrolledBy, array $data): ProgrammeEnrolment
    {
        return DB::transaction(function () use ($programme, $prisoner, $enrolledBy, $data) {
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

            $enrolment = ProgrammeEnrolment::create($data);

            $this->audit->log($enrolledBy, 'enrolled', $enrolment, newValues: [
                'programme' => $programme->name,
                'prisoner' => $prisoner->fullName(),
            ]);

            return $enrolment;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordAttendance(ProgrammeEnrolment $enrolment, User $recordedBy, array $data): ProgrammeAttendance
    {
        return DB::transaction(function () use ($enrolment, $recordedBy, $data) {
            $data['programme_enrolment_id'] = $enrolment->id;
            $data['recorded_by'] = $recordedBy->id;
            $data['attended'] ??= true;

            $attendance = ProgrammeAttendance::create($data);

            $this->audit->log($recordedBy, 'recorded attendance', $attendance, newValues: [
                'session_date' => $attendance->session_date->toDateString(),
                'attended' => $attendance->attended,
            ]);

            return $attendance;
        });
    }

    public function complete(ProgrammeEnrolment $enrolment, User $actor): ProgrammeEnrolment
    {
        return DB::transaction(function () use ($enrolment, $actor) {
            $this->guardActive($enrolment);

            $enrolment->status = EnrolmentStatus::Completed;
            $enrolment->completed_at = now();
            $enrolment->save();

            $this->audit->log($actor, 'completed', $enrolment, newValues: ['status' => 'completed']);

            return $enrolment;
        });
    }

    public function withdraw(ProgrammeEnrolment $enrolment, User $actor, ?string $reason): ProgrammeEnrolment
    {
        return DB::transaction(function () use ($enrolment, $actor, $reason) {
            $this->guardActive($enrolment);

            $enrolment->status = EnrolmentStatus::Withdrawn;
            $enrolment->withdrawal_reason = $reason;
            $enrolment->save();

            $this->audit->log($actor, 'withdrew', $enrolment, newValues: [
                'status' => 'withdrawn',
                'reason' => $enrolment->withdrawal_reason,
            ]);

            return $enrolment;
        });
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
