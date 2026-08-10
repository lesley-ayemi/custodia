<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Block extends Model
{
    public function cells(): HasMany
    {
        return $this->hasMany(Cell::class);
    }
}
