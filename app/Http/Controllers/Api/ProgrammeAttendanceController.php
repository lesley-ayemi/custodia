<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordAttendanceRequest;
use App\Http\Resources\ProgrammeAttendanceResource;
use App\Models\ProgrammeEnrolment;
use App\Services\AuditService;
use App\Services\ProgrammeService;

class ProgrammeAttendanceController extends Controller
{
    public function __construct(
        protected ProgrammeService $programmes,
        protected AuditService $audit,
    ) {}

    public function store(RecordAttendanceRequest $request, ProgrammeEnrolment $programmeEnrolment): ProgrammeAttendanceResource
    {
        $attendance = $this->programmes->recordAttendance($programmeEnrolment, $request->user(), $request->validated());

        $this->audit->log($request->user(), 'recorded attendance', $attendance, newValues: [
            'session_date' => $attendance->session_date->toDateString(),
            'attended' => $attendance->attended,
        ]);

        return new ProgrammeAttendanceResource($attendance->load('recordedBy'));
    }
}
