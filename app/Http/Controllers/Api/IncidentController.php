<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IncidentController extends Controller
{
    public function __construct(
        protected IncidentService $incidents,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Incident::class);

        $query = Incident::query()->with(['prisoner', 'officer', 'resolvedBy']);

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        $incidents = $query->orderByDesc('occurred_at')->paginate(15);

        return IncidentResource::collection($incidents);
    }

    public function store(StoreIncidentRequest $request): IncidentResource
    {
        $incident = $this->incidents->create([
            ...$request->validated(),
            'officer_id' => $request->user()->id,
        ]);

        return new IncidentResource($incident->load('prisoner', 'officer'));
    }

    public function show(Incident $incident): IncidentResource
    {
        $this->authorize('view', $incident);

        return new IncidentResource($incident->load('prisoner', 'officer', 'resolvedBy'));
    }

    public function markUnderReview(Request $request, Incident $incident): IncidentResource
    {
        $this->authorize('review', $incident);

        $this->incidents->markUnderReview($incident);

        return new IncidentResource($incident->load('prisoner', 'officer', 'resolvedBy'));
    }

    public function resolve(Request $request, Incident $incident): IncidentResource
    {
        $this->authorize('review', $incident);

        $this->incidents->resolve($incident, $request->user());

        return new IncidentResource($incident->load('prisoner', 'officer', 'resolvedBy'));
    }
}
