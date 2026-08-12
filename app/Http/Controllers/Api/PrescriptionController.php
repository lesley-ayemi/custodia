<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Models\Prisoner;
use App\Services\MedicalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PrescriptionController extends Controller
{
    public function __construct(
        protected MedicalService $medical,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Prescription::class);

        $prescriptions = $prisoner->prescriptions()->with('prescribedBy')->get();

        return PrescriptionResource::collection($prescriptions);
    }

    public function store(StorePrescriptionRequest $request, Prisoner $prisoner): PrescriptionResource
    {
        $prescription = $this->medical->prescribe($prisoner, $request->user(), $request->validated());

        return new PrescriptionResource($prescription->load('prescribedBy'));
    }

    public function discontinue(Request $request, Prescription $prescription): PrescriptionResource
    {
        $this->authorize('manage', $prescription);

        $this->medical->discontinuePrescription($prescription, $request->user());

        return new PrescriptionResource($prescription->load('prescribedBy'));
    }
}
