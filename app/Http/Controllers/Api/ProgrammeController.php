<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgrammeRequest;
use App\Http\Requests\UpdateProgrammeRequest;
use App\Http\Resources\ProgrammeResource;
use App\Models\Programme;
use App\Services\ProgrammeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProgrammeController extends Controller
{
    public function __construct(
        protected ProgrammeService $programmes,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Programme::class);

        $programmes = Programme::withCount('enrolments')->orderBy('name')->get();

        return ProgrammeResource::collection($programmes);
    }

    public function store(StoreProgrammeRequest $request): ProgrammeResource
    {
        $programme = $this->programmes->createProgramme($request->validated(), $request->user());

        return new ProgrammeResource($programme);
    }

    public function update(UpdateProgrammeRequest $request, Programme $programme): ProgrammeResource
    {
        $this->programmes->updateProgramme($programme, $request->validated(), $request->user());

        return new ProgrammeResource($programme);
    }

    public function destroy(Request $request, Programme $programme): Response
    {
        $this->authorize('manage', Programme::class);

        $this->programmes->deleteProgramme($programme, $request->user());

        return response()->noContent();
    }
}
