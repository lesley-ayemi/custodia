<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockRequest;
use App\Http\Requests\UpdateBlockRequest;
use App\Http\Resources\BlockResource;
use App\Models\Block;
use App\Models\Cell;
use App\Services\AuditService;
use App\Services\HousingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BlockController extends Controller
{
    public function __construct(
        protected HousingService $housing,
        protected AuditService $audit,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Cell::class);

        $blocks = Block::with('cells')->orderBy('name')->get();

        return BlockResource::collection($blocks);
    }

    public function store(StoreBlockRequest $request): BlockResource
    {
        $block = $this->housing->createBlock($request->validated());

        $this->audit->log($request->user(), 'created', $block, newValues: ['name' => $block->name]);

        return new BlockResource($block->loadMissing('cells'));
    }

    public function update(UpdateBlockRequest $request, Block $block): BlockResource
    {
        $oldValues = $block->only(array_keys($request->validated()));

        $this->housing->updateBlock($block, $request->validated());

        $this->audit->log($request->user(), 'updated', $block, oldValues: $oldValues, newValues: $request->validated());

        return new BlockResource($block->loadMissing('cells'));
    }

    public function destroy(Request $request, Block $block): Response
    {
        $this->authorize('manage', Cell::class);

        $name = $block->name;

        $this->housing->deleteBlock($block);

        $this->audit->log($request->user(), 'deleted', $block, oldValues: ['name' => $name]);

        return response()->noContent();
    }
}
