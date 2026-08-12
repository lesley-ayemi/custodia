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
use Illuminate\Validation\ValidationException;

class MedicalService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function addRecord(Prisoner $prisoner, User $recordedBy, array $data): MedicalRecord
    {
        $data['prisoner_id'] = $prisoner->id;
        $data['recorded_by'] = $recordedBy->id;
        $data['recorded_at'] = now();

        return MedicalRecord::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleAppointment(Prisoner $prisoner, User $scheduledBy, array $data): MedicalAppointment
    {
        $data['prisoner_id'] = $prisoner->id;
        $data['scheduled_by'] = $scheduledBy->id;
        $data['status'] ??= MedicalAppointmentStatus::Scheduled;

        return MedicalAppointment::create($data);
    }

    public function completeAppointment(MedicalAppointment $appointment): MedicalAppointment
    {
        $this->guardScheduled($appointment);

        $appointment->status = MedicalAppointmentStatus::Completed;
        $appointment->save();

        return $appointment;
    }

    public function cancelAppointment(MedicalAppointment $appointment): MedicalAppointment
    {
        $this->guardScheduled($appointment);

        $appointment->status = MedicalAppointmentStatus::Cancelled;
        $appointment->save();

        return $appointment;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function prescribe(Prisoner $prisoner, User $prescribedBy, array $data): Prescription
    {
        $data['prisoner_id'] = $prisoner->id;
        $data['prescribed_by'] = $prescribedBy->id;
        $data['status'] ??= PrescriptionStatus::Active;

        return Prescription::create($data);
    }

    public function discontinuePrescription(Prescription $prescription): Prescription
    {
        if ($prescription->status !== PrescriptionStatus::Active) {
            throw ValidationException::withMessages([
                'status' => 'This prescription has already been discontinued.',
            ]);
        }

        $prescription->status = PrescriptionStatus::Discontinued;
        $prescription->end_date = now()->toDateString();
        $prescription->save();

        return $prescription;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addAlert(Prisoner $prisoner, User $createdBy, array $data): MedicalAlert
    {
        $data['prisoner_id'] = $prisoner->id;
        $data['created_by'] = $createdBy->id;
        $data['active'] ??= true;

        return MedicalAlert::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAlert(MedicalAlert $alert, array $data): MedicalAlert
    {
        $alert->update($data);

        return $alert;
    }

    public function resolveAlert(MedicalAlert $alert): MedicalAlert
    {
        $alert->active = false;
        $alert->save();

        return $alert;
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
