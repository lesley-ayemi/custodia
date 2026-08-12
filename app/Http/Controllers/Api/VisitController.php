<?php

namespace App\Http\Controllers\Api;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckOutVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Prisoner;
use App\Models\Visit;
use App\Services\VisitorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VisitController extends Controller
{
    protected const RELATIONS = ['prisoner', 'visitor', 'checkedInBy', 'checkedOutBy'];

    public function __construct(
        protected VisitorService $visitors,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Visit::class);

        $visits = $prisoner->visits()->with(self::RELATIONS)->get();

        return VisitResource::collection($visits);
    }

    public function upcoming(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Visit::class);

        $visits = Visit::query()
            ->with(self::RELATIONS)
            ->where('status', VisitStatus::Scheduled)
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        return VisitResource::collection($visits);
    }

    public function checkIn(Request $request, Visit $visit): VisitResource
    {
        $this->authorize('manage', $visit);

        $this->visitors->checkIn($visit, $request->user());

        return new VisitResource($visit->load(self::RELATIONS));
    }

    public function checkOut(CheckOutVisitRequest $request, Visit $visit): VisitResource
    {
        $this->visitors->checkOut($visit, $request->user(), $request->validated('notes'));

        return new VisitResource($visit->load(self::RELATIONS));
    }

    public function cancel(Request $request, Visit $visit): VisitResource
    {
        $this->authorize('manage', $visit);

        $this->visitors->cancelVisit($visit, $request->user());

        return new VisitResource($visit->load(self::RELATIONS));
    }
}
