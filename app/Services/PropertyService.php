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
    /**
     * @param  array<int, array{description: string, quantity: int, storage_location: string}>  $items
     * @return Collection<int, PropertyItem>
     */
    public function receiveBag(Prisoner $prisoner, array $items, User $receivedBy): Collection
    {
        return DB::transaction(function () use ($prisoner, $items, $receivedBy) {
            $propertyNumber = $this->nextPropertyNumber();
            $receivedAt = now();

            return collect($items)->map(function (array $item) use ($prisoner, $propertyNumber, $receivedBy, $receivedAt) {
                $propertyItem = PropertyItem::create([
                    'prisoner_id' => $prisoner->id,
                    'property_number' => $propertyNumber,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'] ?? 1,
                    'storage_location' => $item['storage_location'],
                    'received_by' => $receivedBy->id,
                    'received_at' => $receivedAt,
                ]);

                return $propertyItem->setRelation('receivedBy', $receivedBy);
            });
        });
    }

    public function releaseItem(PropertyItem $item, User $releasedBy): PropertyItem
    {
        $item->released_by = $releasedBy->id;
        $item->released_at = now();
        $item->save();

        return $item;
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
