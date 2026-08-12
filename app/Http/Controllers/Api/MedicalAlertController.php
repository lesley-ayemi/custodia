<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalAlertRequest;
use App\Http\Requests\UpdateMedicalAlertRequest;
use App\Http\Resources\MedicalAlertResource;
use App\Models\MedicalAlert;
use App\Models\Prisoner;
use App\Services\AuditService;
use App\Services\MedicalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MedicalAlertController extends Controller
{
    public function __construct(
        protected MedicalService $medical,
        protected AuditService $audit,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MedicalAlert::class);

        $alerts = $prisoner->medicalAlerts()->where('active', true)->with('createdBy')->get();

        return MedicalAlertResource::collection($alerts);
    }

    public function store(StoreMedicalAlertRequest $request, Prisoner $prisoner): MedicalAlertResource
    {
        $alert = $this->medical->addAlert($prisoner, $request->user(), $request->validated());

        $this->audit->log($request->user(), 'added medical alert', $alert, newValues: [
            'message' => $alert->message,
            'severity' => $alert->severity->value,
        ]);

        return new MedicalAlertResource($alert->load('createdBy'));
    }

    public function update(UpdateMedicalAlertRequest $request, MedicalAlert $medicalAlert): MedicalAlertResource
    {
        $oldValues = $medicalAlert->only(array_keys($request->validated()));

        $this->medical->updateAlert($medicalAlert, $request->validated());

        $this->audit->log($request->user(), 'updated medical alert', $medicalAlert, oldValues: $oldValues, newValues: $request->validated());

        return new MedicalAlertResource($medicalAlert->load('createdBy'));
    }

    public function resolve(Request $request, MedicalAlert $medicalAlert): MedicalAlertResource
    {
        $this->authorize('manage', $medicalAlert);

        $this->medical->resolveAlert($medicalAlert);

        $this->audit->log($request->user(), 'resolved medical alert', $medicalAlert, newValues: ['active' => false]);

        return new MedicalAlertResource($medicalAlert->load('createdBy'));
    }
}
