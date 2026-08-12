<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalAlertRequest;
use App\Http\Requests\UpdateMedicalAlertRequest;
use App\Http\Resources\MedicalAlertResource;
use App\Models\MedicalAlert;
use App\Models\Prisoner;
use App\Services\MedicalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MedicalAlertController extends Controller
{
    public function __construct(
        protected MedicalService $medical,
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

        return new MedicalAlertResource($alert->load('createdBy'));
    }

    public function update(UpdateMedicalAlertRequest $request, MedicalAlert $medicalAlert): MedicalAlertResource
    {
        $this->medical->updateAlert($medicalAlert, $request->validated(), $request->user());

        return new MedicalAlertResource($medicalAlert->load('createdBy'));
    }

    public function resolve(Request $request, MedicalAlert $medicalAlert): MedicalAlertResource
    {
        $this->authorize('manage', $medicalAlert);

        $this->medical->resolveAlert($medicalAlert, $request->user());

        return new MedicalAlertResource($medicalAlert->load('createdBy'));
    }
}
