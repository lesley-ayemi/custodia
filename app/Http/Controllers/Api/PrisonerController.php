<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrisonerRequest;
use App\Http\Requests\UpdatePrisonerRequest;
use App\Http\Resources\PrisonerResource;
use App\Models\Prisoner;
use App\Services\PrisonerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PrisonerController extends Controller
{
    public function __construct(
        protected PrisonerService $prisoners,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Prisoner::class);

        $query = Prisoner::query()->whereNull('archived_at');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('prisoner_number', 'ilike', "%{$search}%")
                    ->orWhere('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%");
            });
        }

        $prisoners = $query->orderByDesc('admission_date')->paginate(15);

        return PrisonerResource::collection($prisoners);
    }

    public function store(StorePrisonerRequest $request): PrisonerResource
    {
        $prisoner = $this->prisoners->create($request->validated());

        return new PrisonerResource($prisoner);
    }

    public function show(Prisoner $prisoner): PrisonerResource
    {
        $this->authorize('view', $prisoner);

        return new PrisonerResource($prisoner);
    }

    public function update(UpdatePrisonerRequest $request, Prisoner $prisoner): PrisonerResource
    {
        $this->prisoners->update($prisoner, $request->validated());

        return new PrisonerResource($prisoner);
    }

    public function archive(Request $request, Prisoner $prisoner): PrisonerResource
    {
        $this->authorize('archive', $prisoner);

        $this->prisoners->archive($prisoner);

        return new PrisonerResource($prisoner);
    }
}
