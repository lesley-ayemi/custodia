<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordInitialAssessmentRequest;
use App\Http\Requests\RecordLegalAuthorityRequest;
use App\Http\Requests\RecordSecurityClassificationRequest;
use App\Http\Requests\StartAdmissionRequest;
use App\Http\Resources\AdmissionResource;
use App\Models\Admission;
use App\Services\AdmissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdmissionController extends Controller
{
    protected const RELATIONS = ['prisoner', 'admittedBy'];

    public function __construct(
        protected AdmissionService $admissions,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Admission::class);

        $query = Admission::query()->with(self::RELATIONS);

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        $admissions = $query->orderByDesc('admission_date')->paginate(15);

        return AdmissionResource::collection($admissions);
    }

    public function show(Admission $admission): AdmissionResource
    {
        $this->authorize('viewAny', Admission::class);

        return new AdmissionResource($admission->load(self::RELATIONS));
    }

    public function store(StartAdmissionRequest $request): AdmissionResource
    {
        $prisonerData = $request->safe()->only(['first_name', 'last_name', 'date_of_birth', 'gender', 'expected_release_date']);
        $admissionData = $request->safe()->only(['admission_date', 'admission_reason']);

        $admission = $this->admissions->start($prisonerData, $admissionData, $request->user());

        return new AdmissionResource($admission->load(self::RELATIONS));
    }

    public function recordLegalAuthority(RecordLegalAuthorityRequest $request, Admission $admission): AdmissionResource
    {
        $this->admissions->recordLegalAuthority($admission, $request->validated('reference'), $request->user());

        return new AdmissionResource($admission->load(self::RELATIONS));
    }

    public function recordInitialAssessment(RecordInitialAssessmentRequest $request, Admission $admission): AdmissionResource
    {
        $this->admissions->recordInitialAssessment($admission, $request->validated('notes'), $request->user());

        return new AdmissionResource($admission->load(self::RELATIONS));
    }

    public function recordSecurityClassification(RecordSecurityClassificationRequest $request, Admission $admission): AdmissionResource
    {
        $this->admissions->recordSecurityClassification($admission, $request->validated('classification'), $request->user());

        return new AdmissionResource($admission->load(self::RELATIONS));
    }

    public function advanceToMedicalScreening(Request $request, Admission $admission): AdmissionResource
    {
        $this->authorize('manage', $admission);

        $this->admissions->advanceToMedicalScreening($admission, $request->user());

        return new AdmissionResource($admission->load(self::RELATIONS));
    }

    public function completeMedicalScreening(Request $request, Admission $admission): AdmissionResource
    {
        $this->authorize('completeMedicalScreening', $admission);

        $this->admissions->completeMedicalScreening($admission, $request->user());

        return new AdmissionResource($admission->load(self::RELATIONS));
    }

    public function completeHousingAssignment(Request $request, Admission $admission): AdmissionResource
    {
        $this->authorize('manage', $admission);

        $this->admissions->completeHousingAssignment($admission, $request->user());

        return new AdmissionResource($admission->load(self::RELATIONS));
    }
}
