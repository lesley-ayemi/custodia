<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtHearingRequest;
use App\Http\Resources\CourtHearingResource;
use App\Models\CourtCase;
use App\Models\CourtHearing;
use App\Services\CourtService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourtHearingController extends Controller
{
    public function __construct(
        protected CourtService $court,
    ) {}

    public function upcoming(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CourtCase::class);

        $hearings = CourtHearing::query()
            ->with('courtCase.prisoner')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        return CourtHearingResource::collection($hearings);
    }

    public function store(StoreCourtHearingRequest $request, CourtCase $courtCase): CourtHearingResource
    {
        $hearing = $this->court->scheduleHearing($courtCase, $request->validated(), $request->user());

        return new CourtHearingResource($hearing->load('courtCase.prisoner'));
    }
}
