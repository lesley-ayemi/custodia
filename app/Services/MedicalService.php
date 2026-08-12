<?php

namespace App\Services;

use App\Enums\MedicalAppointmentStatus;
use App\Enums\PrescriptionStatus;
use App\Models\MedicalAlert;
use App\Models\MedicalAppointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MedicalService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function addRecord(Prisoner $prisoner, User $recordedBy, array $data): MedicalRecord
    {
        return DB::transaction(function () use ($prisoner, $recordedBy, $data) {
            $data['prisoner_id'] = $prisoner->id;
            $data['recorded_by'] = $recordedBy->id;
            $data['recorded_at'] = now();

            $record = MedicalRecord::create($data);

            $this->audit->log($recordedBy, 'added medical record', $record, newValues: [
                'condition' => $record->condition,
            ]);

            return $record;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleAppointment(Prisoner $prisoner, User $scheduledBy, array $data): MedicalAppointment
    {
        return DB::transaction(function () use ($prisoner, $scheduledBy, $data) {
            $data['prisoner_id'] = $prisoner->id;
            $data['scheduled_by'] = $scheduledBy->id;
            $data['status'] ??= MedicalAppointmentStatus::Scheduled;

            $appointment = MedicalAppointment::create($data);

            $this->audit->log($scheduledBy, 'scheduled medical appointment', $appointment, newValues: [
                'appointment_type' => $appointment->appointment_type,
                'scheduled_at' => $appointment->scheduled_at->toIso8601String(),
            ]);

            return $appointment;
        });
    }

    public function completeAppointment(MedicalAppointment $appointment, User $actor): MedicalAppointment
    {
        return DB::transaction(function () use ($appointment, $actor) {
            $this->guardScheduled($appointment);

            $appointment->status = MedicalAppointmentStatus::Completed;
            $appointment->save();

            $this->audit->log($actor, 'completed medical appointment', $appointment, newValues: ['status' => 'completed']);

            return $appointment;
        });
    }

    public function cancelAppointment(MedicalAppointment $appointment, User $actor): MedicalAppointment
    {
        return DB::transaction(function () use ($appointment, $actor) {
            $this->guardScheduled($appointment);

            $appointment->status = MedicalAppointmentStatus::Cancelled;
            $appointment->save();

            $this->audit->log($actor, 'cancelled medical appointment', $appointment, newValues: ['status' => 'cancelled']);

            return $appointment;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function prescribe(Prisoner $prisoner, User $prescribedBy, array $data): Prescription
    {
        return DB::transaction(function () use ($prisoner, $prescribedBy, $data) {
            $data['prisoner_id'] = $prisoner->id;
            $data['prescribed_by'] = $prescribedBy->id;
            $data['status'] ??= PrescriptionStatus::Active;

            $prescription = Prescription::create($data);

            $this->audit->log($prescribedBy, 'prescribed medication', $prescription, newValues: [
                'medication_name' => $prescription->medication_name,
                'dosage' => $prescription->dosage,
            ]);

            return $prescription;
        });
    }

    public function discontinuePrescription(Prescription $prescription, User $actor): Prescription
    {
        return DB::transaction(function () use ($prescription, $actor) {
            if ($prescription->status !== PrescriptionStatus::Active) {
                throw ValidationException::withMessages([
                    'status' => 'This prescription has already been discontinued.',
                ]);
            }

            $prescription->status = PrescriptionStatus::Discontinued;
            $prescription->end_date = now()->toDateString();
            $prescription->save();

            $this->audit->log($actor, 'discontinued prescription', $prescription, newValues: [
                'medication_name' => $prescription->medication_name,
                'status' => 'discontinued',
            ]);

            return $prescription;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addAlert(Prisoner $prisoner, User $createdBy, array $data): MedicalAlert
    {
        return DB::transaction(function () use ($prisoner, $createdBy, $data) {
            $data['prisoner_id'] = $prisoner->id;
            $data['created_by'] = $createdBy->id;
            $data['active'] ??= true;

            $alert = MedicalAlert::create($data);

            $this->audit->log($createdBy, 'added medical alert', $alert, newValues: [
                'message' => $alert->message,
                'severity' => $alert->severity->value,
            ]);

            return $alert;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAlert(MedicalAlert $alert, array $data, User $actor): MedicalAlert
    {
        return DB::transaction(function () use ($alert, $data, $actor) {
            $oldValues = $alert->only(array_keys($data));

            $alert->update($data);

            $this->audit->log($actor, 'updated medical alert', $alert, oldValues: $oldValues, newValues: $data);

            return $alert;
        });
    }

    public function resolveAlert(MedicalAlert $alert, User $actor): MedicalAlert
    {
        return DB::transaction(function () use ($alert, $actor) {
            $alert->active = false;
            $alert->save();

            $this->audit->log($actor, 'resolved medical alert', $alert, newValues: ['active' => false]);

            return $alert;
        });
    }

    protected function guardScheduled(MedicalAppointment $appointment): void
    {
        if ($appointment->status !== MedicalAppointmentStatus::Scheduled) {
            throw ValidationException::withMessages([
                'status' => 'This appointment has already been finalised.',
            ]);
        }
    }
}
