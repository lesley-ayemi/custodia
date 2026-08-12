<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['block_id', 'name'])]
class Wing extends Model
{
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function cells(): HasMany
    {
        return $this->hasMany(Cell::class);
    }
}
