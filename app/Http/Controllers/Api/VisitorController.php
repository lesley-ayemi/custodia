<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorRequest;
use App\Http\Resources\VisitorResource;
use App\Models\Visitor;
use App\Services\VisitorService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VisitorController extends Controller
{
    public function __construct(
        protected VisitorService $visitors,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Visitor::class);

        $visitors = Visitor::query()->orderBy('name')->get();

        return VisitorResource::collection($visitors);
    }

    public function store(StoreVisitorRequest $request): VisitorResource
    {
        $visitor = $this->visitors->registerVisitor($request->validated(), $request->user());

        return new VisitorResource($visitor);
    }
}
