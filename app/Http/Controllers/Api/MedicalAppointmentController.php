<?php

namespace App\Http\Controllers\Api;

use App\Enums\MedicalAppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalAppointmentRequest;
use App\Http\Resources\MedicalAppointmentResource;
use App\Models\MedicalAppointment;
use App\Models\Prisoner;
use App\Services\MedicalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MedicalAppointmentController extends Controller
{
    public function __construct(
        protected MedicalService $medical,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MedicalAppointment::class);

        $appointments = $prisoner->medicalAppointments()->with('scheduledBy')->get();

        return MedicalAppointmentResource::collection($appointments);
    }

    public function upcoming(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MedicalAppointment::class);

        $appointments = MedicalAppointment::query()
            ->with('prisoner', 'scheduledBy')
            ->where('status', MedicalAppointmentStatus::Scheduled)
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        return MedicalAppointmentResource::collection($appointments);
    }

    public function store(StoreMedicalAppointmentRequest $request, Prisoner $prisoner): MedicalAppointmentResource
    {
        $appointment = $this->medical->scheduleAppointment($prisoner, $request->user(), $request->validated());

        return new MedicalAppointmentResource($appointment->load('scheduledBy'));
    }

    public function complete(Request $request, MedicalAppointment $medicalAppointment): MedicalAppointmentResource
    {
        $this->authorize('manage', $medicalAppointment);

        $this->medical->completeAppointment($medicalAppointment, $request->user());

        return new MedicalAppointmentResource($medicalAppointment->load('scheduledBy'));
    }

    public function cancel(Request $request, MedicalAppointment $medicalAppointment): MedicalAppointmentResource
    {
        $this->authorize('manage', $medicalAppointment);

        $this->medical->cancelAppointment($medicalAppointment, $request->user());

        return new MedicalAppointmentResource($medicalAppointment->load('scheduledBy'));
    }
}
