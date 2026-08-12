<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCellRequest;
use App\Http\Requests\UpdateCellRequest;
use App\Http\Resources\CellResource;
use App\Models\Cell;
use App\Services\HousingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CellController extends Controller
{
    public function __construct(
        protected HousingService $housing,
    ) {}

    public function store(StoreCellRequest $request): CellResource
    {
        $cell = $this->housing->createCell($request->validated(), $request->user());

        return new CellResource($cell);
    }

    public function update(UpdateCellRequest $request, Cell $cell): CellResource
    {
        $this->housing->updateCell($cell, $request->validated(), $request->user());

        return new CellResource($cell);
    }

    public function destroy(Request $request, Cell $cell): Response
    {
        $this->authorize('manage', Cell::class);

        $this->housing->deleteCell($cell, $request->user());

        return response()->noContent();
    }
}
