<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyBagRequest;
use App\Http\Resources\PropertyItemResource;
use App\Models\Prisoner;
use App\Models\PropertyItem;
use App\Services\AuditService;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyItemController extends Controller
{
    public function __construct(
        protected PropertyService $property,
        protected AuditService $audit,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PropertyItem::class);

        $items = $prisoner->propertyItems()->with('receivedBy', 'releasedBy')->get();

        return PropertyItemResource::collection($items);
    }

    public function store(StorePropertyBagRequest $request, Prisoner $prisoner): JsonResponse
    {
        $items = $this->property->receiveBag($prisoner, $request->validated('items'), $request->user());

        $this->audit->log($request->user(), 'received property', $prisoner, newValues: [
            'property_number' => $items->first()->property_number,
            'item_count' => $items->count(),
        ]);

        return PropertyItemResource::collection($items)->response()->setStatusCode(201);
    }

    public function release(Request $request, PropertyItem $propertyItem): PropertyItemResource
    {
        $this->authorize('release', $propertyItem);

        $this->property->releaseItem($propertyItem, $request->user());

        $this->audit->log($request->user(), 'released property', $propertyItem, newValues: [
            'property_number' => $propertyItem->property_number,
            'description' => $propertyItem->description,
        ]);

        return new PropertyItemResource($propertyItem->load('receivedBy', 'releasedBy'));
    }
}
