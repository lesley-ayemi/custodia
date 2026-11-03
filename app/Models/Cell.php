<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['wing_id', 'code', 'capacity'])]
class Cell extends Model
{
    public function wing(): BelongsTo
    {
        return $this->belongsTo(Wing::class);
    }

    public function housingAssignments(): HasMany
    {
        return $this->hasMany(HousingAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->housingAssignments()->whereNull('ended_at');
    }

    public function occupancy(): int
    {
        // withCount('activeAssignments') leaves the number in the attributes, so
        // use it when the caller has already paid for it. Listing a whole block
        // without this costs one count query per cell.
        //
        // It is a snapshot from whenever the parent query ran, which is fine for
        // display. Anything enforcing capacity has to re-read the cell under a
        // row lock, as HousingService::assign does.
        if (array_key_exists('active_assignments_count', $this->attributes)) {
            return (int) $this->attributes['active_assignments_count'];
        }

        return $this->activeAssignments()->count();
    }

    public function availableBeds(): int
    {
        return max(0, $this->capacity - $this->occupancy());
    }
}
