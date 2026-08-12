<?php

namespace App\Http\Controllers\Api;

use App\Enums\MovementStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovementRequest;
use App\Http\Resources\MovementResource;
use App\Models\Movement;
use App\Models\Prisoner;
use App\Services\MovementService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovementController extends Controller
{
    protected const RELATIONS = ['prisoner', 'requestedBy', 'approvedBy'];

    public function __construct(
        protected MovementService $movements,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Movement::class);

        $movements = $prisoner->movements()->with(self::RELATIONS)->get();

        return MovementResource::collection($movements);
    }

    public function upcoming(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Movement::class);

        $movements = Movement::query()
            ->with(self::RELATIONS)
            ->whereNotIn('status', [MovementStatus::Returned, MovementStatus::Cancelled])
            ->orderBy('scheduled_at')
            ->get();

        return MovementResource::collection($movements);
    }

    public function store(StoreMovementRequest $request, Prisoner $prisoner): MovementResource
    {
        $movement = $this->movements->request($prisoner, $request->user(), $request->validated());

        return new MovementResource($movement->load(self::RELATIONS));
    }

    public function approve(Request $request, Movement $movement): MovementResource
    {
        $this->authorize('approve', $movement);

        $this->movements->approve($movement, $request->user());

        return new MovementResource($movement->load(self::RELATIONS));
    }

    public function depart(Request $request, Movement $movement): MovementResource
    {
        $this->authorize('manage', $movement);

        $this->movements->markDeparted($movement, $request->user());

        return new MovementResource($movement->load(self::RELATIONS));
    }

    public function arrive(Request $request, Movement $movement): MovementResource
    {
        $this->authorize('manage', $movement);

        $this->movements->markArrived($movement, $request->user());

        return new MovementResource($movement->load(self::RELATIONS));
    }

    public function markReturned(Request $request, Movement $movement): MovementResource
    {
        $this->authorize('manage', $movement);

        $this->movements->markReturned($movement, $request->user());

        return new MovementResource($movement->load(self::RELATIONS));
    }

    public function cancel(Request $request, Movement $movement): MovementResource
    {
        $this->authorize('manage', $movement);

        $this->movements->cancel($movement, $request->user());

        return new MovementResource($movement->load(self::RELATIONS));
    }
}
