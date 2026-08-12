<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Models\Prisoner;
use App\Services\AuditService;
use App\Services\MedicalService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MedicalRecordController extends Controller
{
    public function __construct(
        protected MedicalService $medical,
        protected AuditService $audit,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MedicalRecord::class);

        $records = $prisoner->medicalRecords()->with('recordedBy')->get();

        return MedicalRecordResource::collection($records);
    }

    public function store(StoreMedicalRecordRequest $request, Prisoner $prisoner): MedicalRecordResource
    {
        $record = $this->medical->addRecord($prisoner, $request->user(), $request->validated());

        $this->audit->log($request->user(), 'added medical record', $record, newValues: [
            'condition' => $record->condition,
        ]);

        return new MedicalRecordResource($record->load('recordedBy'));
    }
}
