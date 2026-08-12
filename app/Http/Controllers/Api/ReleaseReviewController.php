<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReleaseStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveReleaseRequest;
use App\Http\Requests\CancelReleaseReviewRequest;
use App\Http\Requests\InitiateReleaseReviewRequest;
use App\Http\Requests\RecordReleaseStepRequest;
use App\Http\Resources\ReleaseReviewResource;
use App\Models\Prisoner;
use App\Models\ReleaseReview;
use App\Services\ReleaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReleaseReviewController extends Controller
{
    protected const RELATIONS = ['prisoner', 'initiatedBy', 'steps.completedBy'];

    public function __construct(
        protected ReleaseService $releases,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ReleaseReview::class);

        $query = ReleaseReview::query()->with(self::RELATIONS);

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        $reviews = $query->orderByDesc('initiated_at')->paginate(15);

        return ReleaseReviewResource::collection($reviews);
    }

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ReleaseReview::class);

        $reviews = $prisoner->releaseReviews()->with(self::RELATIONS)->get();

        return ReleaseReviewResource::collection($reviews);
    }

    public function store(InitiateReleaseReviewRequest $request, Prisoner $prisoner): ReleaseReviewResource
    {
        $review = $this->releases->initiate($prisoner, $request->user());

        return new ReleaseReviewResource($review->load(self::RELATIONS));
    }

    public function recordLegalVerification(RecordReleaseStepRequest $request, ReleaseReview $releaseReview): ReleaseReviewResource
    {
        return $this->recordStep($request, $releaseReview, ReleaseStep::LegalVerification);
    }

    public function recordSentenceVerification(RecordReleaseStepRequest $request, ReleaseReview $releaseReview): ReleaseReviewResource
    {
        return $this->recordStep($request, $releaseReview, ReleaseStep::SentenceVerification);
    }

    public function recordPropertyVerification(RecordReleaseStepRequest $request, ReleaseReview $releaseReview): ReleaseReviewResource
    {
        return $this->recordStep($request, $releaseReview, ReleaseStep::PropertyVerification);
    }

    public function recordDocumentation(RecordReleaseStepRequest $request, ReleaseReview $releaseReview): ReleaseReviewResource
    {
        return $this->recordStep($request, $releaseReview, ReleaseStep::Documentation);
    }

    public function approveBySupervisor(ApproveReleaseRequest $request, ReleaseReview $releaseReview): ReleaseReviewResource
    {
        $this->releases->completeStep($releaseReview, ReleaseStep::SupervisorApproval, $request->user(), $request->validated('notes'));

        return new ReleaseReviewResource($releaseReview->load(self::RELATIONS));
    }

    public function cancel(CancelReleaseReviewRequest $request, ReleaseReview $releaseReview): ReleaseReviewResource
    {
        $this->releases->cancel($releaseReview, $request->user(), $request->validated('reason'));

        return new ReleaseReviewResource($releaseReview->load(self::RELATIONS));
    }

    protected function recordStep(RecordReleaseStepRequest $request, ReleaseReview $releaseReview, ReleaseStep $step): ReleaseReviewResource
    {
        $this->releases->completeStep($releaseReview, $step, $request->user(), $request->validated('notes'));

        return new ReleaseReviewResource($releaseReview->load(self::RELATIONS));
    }
}
