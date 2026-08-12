<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgrammeRequest;
use App\Http\Requests\UpdateProgrammeRequest;
use App\Http\Resources\ProgrammeResource;
use App\Models\Programme;
use App\Services\AuditService;
use App\Services\ProgrammeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProgrammeController extends Controller
{
    public function __construct(
        protected ProgrammeService $programmes,
        protected AuditService $audit,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Programme::class);

        $programmes = Programme::withCount('enrolments')->orderBy('name')->get();

        return ProgrammeResource::collection($programmes);
    }

    public function store(StoreProgrammeRequest $request): ProgrammeResource
    {
        $programme = $this->programmes->createProgramme($request->validated());

        $this->audit->log($request->user(), 'created', $programme, newValues: [
            'name' => $programme->name,
            'category' => $programme->category->value,
        ]);

        return new ProgrammeResource($programme);
    }

    public function update(UpdateProgrammeRequest $request, Programme $programme): ProgrammeResource
    {
        $oldValues = $programme->only(array_keys($request->validated()));

        $this->programmes->updateProgramme($programme, $request->validated());

        $this->audit->log($request->user(), 'updated', $programme, oldValues: $oldValues, newValues: $request->validated());

        return new ProgrammeResource($programme);
    }

    public function destroy(Request $request, Programme $programme): Response
    {
        $this->authorize('manage', Programme::class);

        $name = $programme->name;

        $this->programmes->deleteProgramme($programme);

        $this->audit->log($request->user(), 'deleted', $programme, oldValues: ['name' => $name]);

        return response()->noContent();
    }
}
