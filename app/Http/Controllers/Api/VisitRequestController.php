<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveVisitRequestRequest;
use App\Http\Requests\RejectVisitRequestRequest;
use App\Http\Requests\StoreVisitRequestRequest;
use App\Http\Resources\VisitRequestResource;
use App\Models\Prisoner;
use App\Models\VisitRequest;
use App\Services\VisitorService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VisitRequestController extends Controller
{
    protected const RELATIONS = ['visitor', 'prisoner', 'requestedBy', 'visit'];

    public function __construct(
        protected VisitorService $visitors,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', VisitRequest::class);

        $requests = $prisoner->visitRequests()->with(self::RELATIONS)->get();

        return VisitRequestResource::collection($requests);
    }

    public function store(StoreVisitRequestRequest $request): VisitRequestResource
    {
        $visitRequest = $this->visitors->requestVisit($request->validated(), $request->user());

        return new VisitRequestResource($visitRequest->load(self::RELATIONS));
    }

    public function approve(ApproveVisitRequestRequest $request, VisitRequest $visitRequest): VisitRequestResource
    {
        $this->visitors->approveRequest($visitRequest, $request->user(), $request->validated('scheduled_at'));

        return new VisitRequestResource($visitRequest->load(self::RELATIONS));
    }

    public function reject(RejectVisitRequestRequest $request, VisitRequest $visitRequest): VisitRequestResource
    {
        $this->visitors->rejectRequest($visitRequest, $request->user(), $request->validated('reason'));

        return new VisitRequestResource($visitRequest->load(self::RELATIONS));
    }
}
