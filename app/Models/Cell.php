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
        return $this->activeAssignments()->count();
    }

    public function availableBeds(): int
    {
        return max(0, $this->capacity - $this->occupancy());
    }
}
