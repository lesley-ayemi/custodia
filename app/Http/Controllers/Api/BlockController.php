<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockRequest;
use App\Http\Requests\UpdateBlockRequest;
use App\Http\Resources\BlockResource;
use App\Models\Block;
use App\Models\Cell;
use App\Services\HousingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BlockController extends Controller
{
    public function __construct(
        protected HousingService $housing,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Cell::class);

        $blocks = Block::with('cells')->orderBy('name')->get();

        return BlockResource::collection($blocks);
    }

    public function store(StoreBlockRequest $request): BlockResource
    {
        $block = $this->housing->createBlock($request->validated(), $request->user());

        return new BlockResource($block->loadMissing('cells'));
    }

    public function update(UpdateBlockRequest $request, Block $block): BlockResource
    {
        $this->housing->updateBlock($block, $request->validated(), $request->user());

        return new BlockResource($block->loadMissing('cells'));
    }

    public function destroy(Request $request, Block $block): Response
    {
        $this->authorize('manage', Cell::class);

        $this->housing->deleteBlock($block, $request->user());

        return response()->noContent();
    }
}
