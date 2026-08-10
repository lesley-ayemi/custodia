<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlockResource;
use App\Models\Block;
use App\Models\Cell;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlockController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Cell::class);

        $blocks = Block::with('cells')->orderBy('name')->get();

        return BlockResource::collection($blocks);
    }
}
