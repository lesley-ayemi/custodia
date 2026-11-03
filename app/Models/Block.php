<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Relation;

#[Fillable(['facility_id', 'name'])]
class Block extends Model
{
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function wings(): HasMany
    {
        return $this->hasMany(Wing::class);
    }

    public function cells(): HasManyThrough
    {
        return $this->hasManyThrough(Cell::class, Wing::class);
    }

    /**
     * Eager-load spec for a block's wings and cells with each cell's occupancy
     * already counted. Without the count, rendering a block runs a count query
     * per cell, so pass this to with() or load() rather than plain 'wings.cells'.
     *
     * @return array<string, callable>
     */
    public static function estateEagerLoad(): array
    {
        return [
            'wings.cells' => fn (Relation $cells) => $cells->withCount('activeAssignments'),
        ];
    }
}
