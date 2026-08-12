<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
}
