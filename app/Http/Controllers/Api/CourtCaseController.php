<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtCaseRequest;
use App\Http\Resources\CourtCaseResource;
use App\Models\CourtCase;
use App\Models\Prisoner;
use App\Services\CourtService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourtCaseController extends Controller
{
    public function __construct(
        protected CourtService $court,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CourtCase::class);

        $cases = $prisoner->courtCases()->with('legalRepresentative', 'hearings')->get();

        return CourtCaseResource::collection($cases);
    }

    public function store(StoreCourtCaseRequest $request, Prisoner $prisoner): CourtCaseResource
    {
        $case = $this->court->createCase($prisoner, $request->validated(), $request->user());

        return new CourtCaseResource($case->load('legalRepresentative', 'hearings'));
    }

    public function show(CourtCase $courtCase): CourtCaseResource
    {
        $this->authorize('view', $courtCase);

        return new CourtCaseResource($courtCase->load('prisoner', 'legalRepresentative', 'hearings'));
    }
}
