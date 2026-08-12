<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrolPrisonerRequest;
use App\Http\Requests\WithdrawEnrolmentRequest;
use App\Http\Resources\ProgrammeEnrolmentResource;
use App\Models\Prisoner;
use App\Models\Programme;
use App\Models\ProgrammeEnrolment;
use App\Services\AuditService;
use App\Services\ProgrammeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProgrammeEnrolmentController extends Controller
{
    public function __construct(
        protected ProgrammeService $programmes,
        protected AuditService $audit,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ProgrammeEnrolment::class);

        $enrolments = $prisoner->programmeEnrolments()->with('programme', 'enrolledBy', 'attendances')->get();

        return ProgrammeEnrolmentResource::collection($enrolments);
    }

    public function store(EnrolPrisonerRequest $request, Prisoner $prisoner): ProgrammeEnrolmentResource
    {
        $programme = Programme::findOrFail($request->validated('programme_id'));

        $enrolment = $this->programmes->enrol($programme, $prisoner, $request->user(), [
            'enrolled_at' => $request->validated('enrolled_at'),
        ]);

        $this->audit->log($request->user(), 'enrolled', $enrolment, newValues: [
            'programme' => $programme->name,
            'prisoner' => $prisoner->fullName(),
        ]);

        return new ProgrammeEnrolmentResource($enrolment->load('programme', 'enrolledBy', 'attendances'));
    }

    public function complete(Request $request, ProgrammeEnrolment $programmeEnrolment): ProgrammeEnrolmentResource
    {
        $this->authorize('manage', $programmeEnrolment);

        $this->programmes->complete($programmeEnrolment);

        $this->audit->log($request->user(), 'completed', $programmeEnrolment, newValues: ['status' => 'completed']);

        return new ProgrammeEnrolmentResource($programmeEnrolment->load('programme', 'enrolledBy', 'attendances'));
    }

    public function withdraw(WithdrawEnrolmentRequest $request, ProgrammeEnrolment $programmeEnrolment): ProgrammeEnrolmentResource
    {
        $this->programmes->withdraw($programmeEnrolment, $request->validated('reason'));

        $this->audit->log($request->user(), 'withdrew', $programmeEnrolment, newValues: [
            'status' => 'withdrawn',
            'reason' => $programmeEnrolment->withdrawal_reason,
        ]);

        return new ProgrammeEnrolmentResource($programmeEnrolment->load('programme', 'enrolledBy', 'attendances'));
    }
}
