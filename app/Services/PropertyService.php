<?php

namespace App\Services;

use App\Models\Prisoner;
use App\Models\PropertyItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<int, array{description: string, quantity?: int, storage_location: string, notes?: string|null}>  $items
     * @return Collection<int, PropertyItem>
     */
    public function receiveBag(Prisoner $prisoner, array $items, User $receivedBy): Collection
    {
        return DB::transaction(function () use ($prisoner, $items, $receivedBy) {
            $propertyNumber = $this->nextPropertyNumber();
            $receivedAt = now();

            $propertyItems = collect($items)->map(function (array $item) use ($prisoner, $propertyNumber, $receivedBy, $receivedAt) {
                $propertyItem = PropertyItem::create([
                    'prisoner_id' => $prisoner->id,
                    'property_number' => $propertyNumber,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'] ?? 1,
                    'storage_location' => $item['storage_location'],
                    'notes' => $item['notes'] ?? null,
                    'received_by' => $receivedBy->id,
                    'received_at' => $receivedAt,
                ]);

                return $propertyItem->setRelation('receivedBy', $receivedBy);
            });

            $this->audit->log($receivedBy, 'received property', $prisoner, newValues: [
                'property_number' => $propertyNumber,
                'item_count' => $propertyItems->count(),
            ]);

            return $propertyItems;
        });
    }

    public function releaseItem(PropertyItem $item, User $releasedBy, string $releasedTo): PropertyItem
    {
        return DB::transaction(function () use ($item, $releasedBy, $releasedTo) {
            $item->released_by = $releasedBy->id;
            $item->released_to = $releasedTo;
            $item->released_at = now();
            $item->save();

            $this->audit->log($releasedBy, 'released property', $item, newValues: [
                'property_number' => $item->property_number,
                'description' => $item->description,
                'released_to' => $item->released_to,
            ]);

            return $item;
        });
    }

    protected function nextPropertyNumber(): string
    {
        $year = Carbon::now()->year;

        $sequence = DB::table('property_items')
            ->where('property_number', 'like', "PB-{$year}-%")
            ->distinct()
            ->count('property_number') + 1;

        return sprintf('PB-%d-%04d', $year, $sequence);
    }
}
