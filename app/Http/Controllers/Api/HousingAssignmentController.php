<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignHousingRequest;
use App\Http\Resources\HousingAssignmentResource;
use App\Models\Cell;
use App\Models\Prisoner;
use App\Services\AuditService;
use App\Services\HousingService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HousingAssignmentController extends Controller
{
    public function __construct(
        protected HousingService $housing,
        protected AuditService $audit,
    ) {}

    public function store(AssignHousingRequest $request): HousingAssignmentResource
    {
        $prisoner = Prisoner::findOrFail($request->validated('prisoner_id'));
        $cell = Cell::findOrFail($request->validated('cell_id'));

        $previousCell = $prisoner->currentHousing?->cell?->code;

        $assignment = $this->housing->assign($prisoner, $cell, $request->user());

        $this->audit->log(
            $request->user(),
            'housing assignment changed',
            $prisoner,
            oldValues: ['cell' => $previousCell],
            newValues: ['cell' => $cell->code],
        );

        return new HousingAssignmentResource($assignment->load('cell.block', 'assignedBy'));
    }

    public function history(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('view', $prisoner);

        $history = $prisoner->housingAssignments()->with('cell.block', 'assignedBy')->get();

        return HousingAssignmentResource::collection($history);
    }
}
