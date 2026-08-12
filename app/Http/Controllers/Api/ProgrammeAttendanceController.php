<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordAttendanceRequest;
use App\Http\Resources\ProgrammeAttendanceResource;
use App\Models\ProgrammeEnrolment;
use App\Services\ProgrammeService;

class ProgrammeAttendanceController extends Controller
{
    public function __construct(
        protected ProgrammeService $programmes,
    ) {}

    public function store(RecordAttendanceRequest $request, ProgrammeEnrolment $programmeEnrolment): ProgrammeAttendanceResource
    {
        $attendance = $this->programmes->recordAttendance($programmeEnrolment, $request->user(), $request->validated());

        return new ProgrammeAttendanceResource($attendance->load('recordedBy'));
    }
}
