<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCellRequest;
use App\Http\Requests\UpdateCellRequest;
use App\Http\Resources\CellResource;
use App\Models\Cell;
use App\Services\AuditService;
use App\Services\HousingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CellController extends Controller
{
    public function __construct(
        protected HousingService $housing,
        protected AuditService $audit,
    ) {}

    public function store(StoreCellRequest $request): CellResource
    {
        $cell = $this->housing->createCell($request->validated());

        $this->audit->log($request->user(), 'created', $cell, newValues: [
            'code' => $cell->code,
            'capacity' => $cell->capacity,
        ]);

        return new CellResource($cell);
    }

    public function update(UpdateCellRequest $request, Cell $cell): CellResource
    {
        $oldValues = $cell->only(array_keys($request->validated()));

        $this->housing->updateCell($cell, $request->validated());

        $this->audit->log($request->user(), 'updated', $cell, oldValues: $oldValues, newValues: $request->validated());

        return new CellResource($cell);
    }

    public function destroy(Request $request, Cell $cell): Response
    {
        $this->authorize('manage', Cell::class);

        $code = $cell->code;

        $this->housing->deleteCell($cell);

        $this->audit->log($request->user(), 'deleted', $cell, oldValues: ['code' => $code]);

        return response()->noContent();
    }
}
