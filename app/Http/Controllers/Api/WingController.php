<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWingRequest;
use App\Http\Requests\UpdateWingRequest;
use App\Http\Resources\WingResource;
use App\Models\Cell;
use App\Models\Wing;
use App\Services\HousingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WingController extends Controller
{
    public function __construct(
        protected HousingService $housing,
    ) {}

    public function store(StoreWingRequest $request): WingResource
    {
        $wing = $this->housing->createWing($request->validated(), $request->user());

        return new WingResource($wing);
    }

    public function update(UpdateWingRequest $request, Wing $wing): WingResource
    {
        $this->housing->updateWing($wing, $request->validated(), $request->user());

        return new WingResource($wing);
    }

    public function destroy(Request $request, Wing $wing): Response
    {
        $this->authorize('manage', Cell::class);

        $this->housing->deleteWing($wing, $request->user());

        return response()->noContent();
    }
}
